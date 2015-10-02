<?php namespace App\Jobs\Repo;

use App\Jobs\Job;
use App\Model\Repo;
use Gitonomy\Git\Admin;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Filesystem\Filesystem;

/**
 * Class CloneRepoJob
 */
class CloneRepoJob extends Job implements ShouldQueue, SelfHandling
{
    /**
     * @var Repo
     */
    private $repo;

    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @param Repo $repo
     */
    function __construct(Repo $repo)
    {
        $this->repo = $repo;
        $this->fs = new Filesystem();
    }

    public function handle()
    {
        $path = $this->repo->repoPath();

        if (!$this->fs->isDirectory($path) && !$this->fs->makeDirectory($path, 0755, true)) {
            throw new \RuntimeException(sprintf("Cannot create repo dir %s", $path));
        }

        Admin::cloneTo($path, $this->repo->url);
        $this->repo->git()->run('config', ['remote.origin.fetch', 'refs/heads/*:refs/heads/*']);
    }
}
