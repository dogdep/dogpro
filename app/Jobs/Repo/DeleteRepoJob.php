<?php namespace App\Jobs\Repo;

use App\Model\Repo;
use App\Traits\ManageFilesystem;
use DB;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Class DeleteRepoJob
 * @package App\Jobs\Repo
 */
class DeleteRepoJob implements SelfHandling, ShouldQueue
{
    use ManageFilesystem;

    /**
     * @var Repo
     */
    private $repo;

    /**
     * @param Repo $repo
     */
    public function __construct(Repo $repo)
    {
        $this->repo = $repo;
    }

    /**
     * @return bool
     */
    public function handle()
    {
        if (!$this->removeDir($this->repo->repoPath())) {
            throw new \RuntimeException(sprintf("Failed to delete %s", $this->repo->repoPath()));
        }

        if (!$this->removeDir($this->repo->releasePath())) {
            throw new \RuntimeException(sprintf("Failed to delete %s", $this->repo->releasePath()));
        }

        $this->repo->delete();
    }
}
