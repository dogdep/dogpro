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
        $path = $this->repo->repoPath();
        if ($this->fs()->isDirectory($path) && !$this->fs()->deleteDirectory($path)) {
            throw new \RuntimeException(sprintf("Failed to delete %s", $path));
        }

        $path = $this->repo->releasePath();
        if ($this->fs()->isDirectory($path) && !$this->fs()->deleteDirectory($path)) {
            throw new \RuntimeException(sprintf("Failed to delete %s", $path));
        }

        $this->repo->delete();
    }
}
