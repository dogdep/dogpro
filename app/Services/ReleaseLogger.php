<?php namespace App\Services;

use App\Model\Release;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ReleaseLogger
 */
class ReleaseLogger
{
    /**
     * @var Release
     */
    private $release;

    /**
     * @var \Pusher
     */
    private $pusher;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @param Release $release
     * @param \Pusher $pusher
     * @param OutputInterface $output
     */
    public function __construct(Release $release, \Pusher $pusher = null, OutputInterface $output = null)
    {
        $this->release = $release;
        $this->output = $output;
        $this->pusher = $pusher ? : app(\Pusher::class);
    }

    public function error($message, \Exception $exception = null)
    {
        $this->write("<error>$message</error>");
        if (!is_null($exception)) {
            $this->write("<error>{$exception->getMessage()}</error>");
        }
        $this->pushRelease([]);
    }

    public function info($string, array $data = [])
    {
        $this->write($string);
        $this->pushRelease($data);
    }

    public function push()
    {
        $this->pushRelease([]);
    }

    /**
     * @param OutputInterface $output
     */
    public function setOutput($output)
    {
        $this->output = $output;
    }

    public function comment($string, array $data = [])
    {
        $this->write("<comment>$string</comment>");
        $this->pushRelease($data);
    }

    protected function pushRelease(array $data = [])
    {
        $this->pusher->trigger(['releases'], "release-" . $this->release->id, $this->release->toArray() + $data);
    }

    private function write($message)
    {
        if (!is_null($this->output)) {
            $this->output->writeln($message);
        }
    }
}
