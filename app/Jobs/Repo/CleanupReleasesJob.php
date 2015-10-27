<?php namespace App\Jobs\Repo;

use App\Model\Repo;
use App\Traits\ManageFilesystem;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Symfony\Component\Finder\Finder;

/**
 * Class CleanupReleasesJob
 */
class CleanupReleasesJob implements SelfHandling, ShouldQueue
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

    public function handle()
    {
        $dirs = iterator_to_array(Finder::create()->in($this->repo->releasePath())->depth(0)->sortByChangedTime());
        foreach (array_slice(array_reverse($dirs), 3) as $dir) {
            $this->fs()->deleteDirectory($dir);
        }
    }
}
