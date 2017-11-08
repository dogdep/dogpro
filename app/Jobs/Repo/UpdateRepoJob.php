<?php namespace App\Jobs\Repo;


use App\Jobs\Job;
use App\Jobs\Release\PrepareReleaseJob;
use App\Model\Inventory;
use App\Model\Release;
use App\Model\Repo;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Pusher\Pusher;

/**
 * Class UpdateCommand
 */
class UpdateRepoJob extends Job implements ShouldQueue, SelfHandling
{
    use DispatchesJobs;

    /**
     * @var Repo
     */
    private $repo;

    /**
     * @var array
     */
    private $commitList;

    /**
     * @param Repo $repo
     * @param array $commitList
     */
    public function __construct(Repo $repo, $commitList = [])
    {
        $this->repo = $repo;
        $this->commitList = $commitList;
    }

    /**
     * Run pull or clone if repo is not initialized yet
     * @param Pusher $pusher
     */
    public function handle(Pusher $pusher)
    {
        if (is_null($this->repo->git())) {
            $this->dispatch(new CloneRepoJob($this->repo));
            return;
        }

        $this->repo->git()->run('fetch', ['origin', '+refs/heads/*:refs/heads/*', '--prune']);

        $this->checkCommitsForDeployTags();
        $pusher->trigger(['pulls'], 'repo-' . $this->repo->id, []);
    }

    public function checkCommitsForDeployTags()
    {
        $inv = null;
        $commit = null;

        foreach ($this->commitList as $commit) {
            $parts = explode(" ", $commit['message']);

            if (($index = array_search('@deploy', $parts)) !== false) {
                if (isset($parts[$index + 1])) {
                    $inv = $this->repo->inventories()->where('name', trim($parts[$index + 1]))->get()->first();

                    if ($inv) {
                        break;
                    }
                }
            }
        }

        if ($inv instanceof Inventory && !empty($commit['hash'])) {
            $this->dispatch(new PrepareReleaseJob(Release::create([
                'repo_id' => $this->repo->id,
                'commit' => $commit['hash'],
                'status' => Release::QUEUED,
                'roles' => ['dogpro.deploy'],
                'inventory_id' => $inv->id,
            ])));
        }
    }
}
