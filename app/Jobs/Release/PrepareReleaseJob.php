<?php namespace App\Jobs\Release;

use App\Ansible\Config\PlaybookConfig;
use App\Exceptions\ReleaseException;
use App\Jobs\Ansible\PlaybookJob;
use App\Jobs\Job;
use App\Jobs\Repo\CleanupReleasesJob;
use App\Model\Release;
use App\Traits\ManageFilesystem;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Pusher;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Class PrepareReleaseJob
 */
class PrepareReleaseJob extends Job implements ShouldQueue, SelfHandling
{
    use ManageFilesystem;
    use DispatchesJobs;

    /**
     * @var Release
     */
    private $release;

    /**
     * @param Release $release
     */
    public function __construct(Release $release)
    {
        $this->release = $release->init();
    }

    public function handle(Pusher $pusher)
    {
        try {
            if ($this->wasCancelled()) {
                return;
            }

            $this->release->update(['status' => Release::PREPARING]);
            $pusher->trigger('releases', "release-" . $this->release->id, $this->release->toArray());

            $this->prepareReleaseDir();
            $this->createArchive();
            $this->extractArchive();
            $this->writePlaybooks();

            if ($this->wasCancelled()) {
                return;
            }

            $this->dispatch(new PlaybookJob($this->release));
            $this->dispatch(new CleanupReleasesJob($this->release->repo));
        } catch (\Exception $e) {
            $this->release->update(['status' => Release::ERROR, 'raw_logs'=>$e->getMessage()]);
            $pusher->trigger('releases', "release-" . $this->release->id, $this->release->toArray());
            $this->release->logger()->error($e->getMessage());
            throw $e;
        }
    }

    private function createArchive()
    {
        $this->release->repo->git()->run("archive", [$this->release->commit, "-o", $this->release->path("build.tar.gz")]);
    }

    /**
     * @throws ReleaseException
     */
    private function extractArchive()
    {
        $process = ProcessBuilder::create(["tar", "-xvf", $this->release->path("build.tar.gz")])
            ->setWorkingDirectory($this->release->path())
            ->getProcess();

        if ($process->run() !== 0) {
            throw new ReleaseException($this->release, "Release failed, export repo: " . $process->getErrorOutput());
        }
    }

    /**
     * @return string
     * @throws ReleaseException
     */
    private function prepareReleaseDir()
    {
        $releaseDir = $this->release->path();

        if ($this->fs()->isDirectory($releaseDir) && !$this->fs()->deleteDirectory($releaseDir)) {
            throw new ReleaseException($this->release, "Cannot remove existing release!");
        }

        if (!$this->fs()->makeDirectory($releaseDir, 0777, true, true) || !chmod($releaseDir, 0777)) {
            throw new ReleaseException($this->release, "Cannot create release dir!");
        }

        return $releaseDir;
    }

    private function wasCancelled()
    {
        return \DB::table('releases')->where('id', $this->release->id)->value('status') == Release::CANCELLED;
    }


    /**
     * @throws \App\Exceptions\InvalidConfigException
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function writePlaybooks()
    {
        $this->fs()->put($this->release->path("empty.yml"), "");

        $playbook = new PlaybookConfig("all");

        $config = $this->release->config();

        foreach ($config->roles() as $play) {
            if (in_array($play->name(), $this->release->roles)) {
                $play->setSudo(true);
                $playbook->add($play);
            }
        }

        $playbook->setVars([
            "project_name" => $this->release->repo->name,
            "global" => array_merge($config->defaults(), $config->globals(), (array)$this->release->repo->params),
            "build_tar" => $this->release->path("build.tar.gz"),
            "build_path" => $this->release->path(),
            "build_version" => $this->release->commit,
            "build_version_short" => $this->release->commit()->getShortHash(),
            "inventory_name" => $this->release->inventory->name,
        ]);

        $this->release->write($playbook, $this->release->inventory, $this->release->commit());
    }
}
