<?php namespace App\Jobs\Release;

use App\Ansible\Config\PlaybookConfig;
use App\Exceptions\ReleaseException;
use App\Jobs\Ansible\PlaybookJob;
use App\Jobs\Job;
use App\Jobs\Repo\CleanupReleasesJob;
use App\Model\Release;
use App\Traits\ManageFilesystem;
use Illuminate\Bus\Dispatcher;
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

    /**
     * @var Release
     */
    private $release;

    /**
     * @var bool
     */
    private $sync;

    /**
     * @param Release $release
     * @param bool $sync
     */
    public function __construct(Release $release, $sync = false)
    {
        $this->release = $release;
        $this->sync = $sync;
    }

    /**
     * @param Dispatcher $bus
     * @throws \Exception
     */
    public function handle(Dispatcher $bus)
    {
        try {
            if ($this->release->isCancelled()) {
                return;
            }

            $this->release->update(['status' => Release::PREPARING]);
            $this->release->logger()->comment("Preparing release...");

            $this->prepareReleaseDir();
            $this->createArchive();
            $this->extractArchive();
            $this->writePlaybooks();

            if ($this->release->isCancelled()) {
                return;
            }

            if ($this->sync) {
                $bus->dispatchNow(new PlaybookJob($this->release));
                $bus->dispatchNow(new CleanupReleasesJob($this->release->repo));
            } else {
                $bus->dispatch(new PlaybookJob($this->release));
                $bus->dispatch(new CleanupReleasesJob($this->release->repo));
            }
        } catch (\Exception $e) {
            $this->release->update(['status' => Release::ERROR, 'raw_logs'=>$e->getMessage()]);
            $this->release->logger()->push();
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

    /**
     * @throws \App\Exceptions\InvalidConfigException
     * @throws ReleaseException
     */
    private function writePlaybooks()
    {
        $this->fs()->put($this->release->path("empty.yml"), "");

        $playbook = new PlaybookConfig();
        $config = $this->release->config();

        foreach ($config->roles() as $play) {
            if (in_array($play->name(), $this->release->roles) || in_array($play->role(), $this->release->roles)) {
                $play->setSudo(true);
                $playbook->add($play);
            }
        }

        $playbook->setVars([
            "project_name" => $this->release->repo->name,
            "global" => array_merge($config->defaults(), $config->globals(), (array) $this->release->repo->params),
            "build_tar" => $this->release->path("build.tar.gz"),
            "build_path" => $this->release->path(),
            "build_version" => $this->release->commit,
            "build_version_short" => $this->release->commit()->getShortHash(),
            "inventory_name" => $this->release->inventory->name,
        ] + $config->getVars());

        $playbookFile = $this->release->path(Release::PLAYBOOK_FILENAME);
        if (!$this->fs()->put($playbookFile, $playbook->render())) {
            throw new ReleaseException($this->release, "Cannot write playbook file: $playbookFile!");
        }

        $inventoryFile = $this->release->path(Release::INVENTORY_FILENAME);
        if (!$this->fs()->put($inventoryFile, $this->release->inventory->render())) {
            throw new ReleaseException($this->release, "Cannot write inventory file: $inventoryFile!");
        }
    }
}
