<?php namespace App\Jobs\Ansible;

use App\Ansible\Ansible;
use App\Exceptions\AnsibleException;
use App\Model\Release;
use App\Services\NotifierService;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Pusher;
use Symfony\Component\Process\Process;

/**
 * Class PlaybookJob
 */
class PlaybookJob implements ShouldQueue, SelfHandling
{
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

    public function handle(Pusher $pusher)
    {
        $timeStarted = time();
        $key = $this->writePrivateKey();

        try {
            $this->release->resetLogs();
            $this->release->update(['status' => Release::RUNNING, 'started_at'=>new \DateTime()]);
            $ansible = new Ansible(
                $this->release->path(),
                $this->release->inventoryFilename(),
                $this->release->playbookFilename(),
                $this->release->inventory->params + [
                    'private_key' => $key,
                ]
            );

            $process = $ansible->play();
            $process->start();

            $lastOut = $process->getOutput();
            while ($process->isRunning() && !$this->wasCancelled()) {
                $out = $process->getOutput();

                if ($lastOut != $out) {
                    $this->updateRelease($process);
                    $pusher->trigger('releases', "release-" . $this->release->id, $this->release->toArray());
                    $lastOut = $out;
                }

                sleep(1);
            }

            if ($process->isRunning()) {
                $process->stop(0);
            }

            @unlink($key);

            $this->updateRelease($process);

            if ($this->release->status == Release::CANCELLED) {
                $pusher->trigger('releases', "release-" . $this->release->id, $this->release->toArray());
            } elseif ($process->getExitCode() == 0) {
                $this->notifier()->notifySuccess($this->release);
                $this->release->update(['status' => Release::COMPLETED, 'time'=>time()-$timeStarted]);
                $pusher->trigger('releases', "release-" . $this->release->id, $this->release->toArray());
            } else {
                $this->notifier()->notifyFailure($this->release, $process->getErrorOutput());
                throw new AnsibleException($this->release, $ansible, $process->getErrorOutput());
            }
        } catch (\Exception $e) {
            $this->release->logger()->error("Ansible run failed");
            $this->release->update(['status' => Release::ERROR, 'time'=>time()-$timeStarted]);
            $pusher->trigger('releases', "release-" . $this->release->id, $this->release->toArray());

            @unlink($key);
            throw $e;
        }
    }

    /**
     * @return string
     */
    protected function writePrivateKey()
    {
        $file = tempnam(sys_get_temp_dir(), "key");
        @file_put_contents($file, $this->release->inventory->private_key);
        return $file;
    }

    private function wasCancelled()
    {
        $this->release->status = \DB::table('releases')->where('id', $this->release->id)->value('status');
        return $this->release->status == Release::CANCELLED;
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
        $this->release->update(['raw_log' => $process->getOutput() . PHP_EOL . $process->getErrorOutput()]);
    }
}
