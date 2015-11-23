<?php namespace App\Jobs\Ansible;

use App\Ansible\Ansible;
use App\Exceptions\AnsibleException;
use App\Model\Release;
use App\Services\NotifierService;
use App\Traits\ManageFilesystem;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Symfony\Component\Process\Process;

/**
 * Class PlaybookJob
 */
class PlaybookJob implements ShouldQueue, SelfHandling
{
    use ManageFilesystem;

    /**
     * @var Release
     */
    private $release;

    /**
     * @param Release $release
     */
    public function __construct(Release $release)
    {
        $this->release = $release;
    }

    public function handle()
    {
        $timeStarted = time();
        $key = $this->writePrivateKey();

        try {
            $this->release->update(['status' => Release::RUNNING, 'started_at'=>new \DateTime(), 'raw_log' => '']);
            $this->release->logger()->comment("Starting release...");

            $ansible = new Ansible(
                $this->release->path(),
                Release::INVENTORY_FILENAME,
                Release::PLAYBOOK_FILENAME,
                $this->release->inventory->params + $this->release->params + [
                    'private_key' => $key,
                ]
            );

            $process = $ansible->play();
            $process->start();

            $lastOut = $process->getOutput();
            while ($process->isRunning() && !$this->release->isCancelled()) {
                $out = $process->getOutput();

                if ($lastOut != $out) {
                    $this->updateRelease($process);
                    $lastOut = $out;
                }

                sleep(1);
            }

            if ($process->isRunning()) {
                $process->stop(0);
            }

            $this->fs()->delete($key);

            $this->updateRelease($process);

            if ($this->release->status == Release::CANCELLED) {
                $this->release->logger()->warning("Release cancelled");
            } elseif ($process->getExitCode() == 0) {
                $this->notifier()->notifySuccess($this->release);
                $this->release->update(['status' => Release::COMPLETED, 'time'=>time() - $timeStarted]);
                $this->release->logger()->info("Release completed");
            } else {
                $this->notifier()->notifyFailure($this->release, $process->getErrorOutput());
                throw new AnsibleException($this->release, $ansible, $process->getErrorOutput());
            }
        } catch (\Exception $e) {
            $this->release->update(['status' => Release::ERROR, 'time'=>time() - $timeStarted]);
            $this->release->logger()->push();

            $this->fs()->delete($key);
            throw $e;
        }
    }

    /**
     * @return string
     */
    protected function writePrivateKey()
    {
        $file = tempnam(sys_get_temp_dir(), "key");
        if ($this->fs()->put($file, $this->release->inventory->private_key) === false) {
            throw new \RuntimeException("Failed to write private key");
        }
        return $file;
    }

    /**
     * @return NotifierService
     */
    public function notifier()
    {
        return app(NotifierService::class);
    }

    /**
     * @param Process $process
     * @return callable
     */
    public function updateRelease(Process $process)
    {
        $out = $process->getIncrementalOutput() . $process->getIncrementalErrorOutput();
        $this->release->update(['raw_log' => $process->getOutput() . PHP_EOL . $process->getErrorOutput()]);

        if (!empty($out)) {
            $this->release->logger()->info($out);
        }
    }
}
