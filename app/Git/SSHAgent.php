<?php namespace App\Git;

use Symfony\Component\Process\ProcessBuilder;

/**
 * Class SSHAgent
 */
class SSHAgent
{
    /**
     * @var int
     */
    private $pid;

    /**
     * @var string
     */
    private $socket;

    /**
     * @param int $pid
     * @param string $socket
     */
    public function __construct($pid, $socket)
    {
        $this->pid = $pid;
        $this->socket = $socket;
    }

    public static function start()
    {
        $out = ProcessBuilder::create(["ssh-agent", "-c"])->getProcess()->mustRun()->getOutput();

        if (!preg_match('/^setenv SSH_AGENT_PID (\d+);$/m', $out, $pid)) {
            throw new \RuntimeException("Failed to parse SSH Agent response");
        }

        if (!preg_match('/^setenv SSH_AUTH_SOCK (.*);$/m', $out, $sock)) {
            throw new \RuntimeException("Failed to parse SSH Agent response");
        }

        return new SSHAgent($pid[1], $sock[1]);
    }

    public function add($key)
    {
        ProcessBuilder::create(["ssh-add", $key])
            ->setEnv('SSH_AGENT_PID', $this->pid)
            ->setEnv('SSH_AUTH_SOCK', $this->socket)
            ->getProcess()
            ->mustRun();

        return $this;
    }

    /**
     * @return int
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * @return string
     */
    public function getSocket()
    {
        return $this->socket;
    }

    public function kill()
    {
        posix_kill($this->pid, SIGTERM);
    }
}
