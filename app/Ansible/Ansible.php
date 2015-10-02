<?php namespace App\Ansible;

use Symfony\Component\Process\ProcessBuilder;

/**
 * Ansible command factory
 *
 * @package App\Ansible
 * @author Marc Aschmann <maschmann@gmail.com>
 */
class Ansible
{
    /**
     * @var string
     */
    private $dir;

    /**
     * @var string
     */
    private $inventoryFile;

    /**
     * @var string
     */
    private $playbookFile;

    /**
     * @var array
     */
    private $options;

    /**
     * @param string $dir
     * @param string $inventoryFile
     * @param string $playbookFile
     * @param array $options
     */
    function __construct($dir, $inventoryFile, $playbookFile, array $options = [])
    {
        $this->dir = $dir;
        $this->inventoryFile = $inventoryFile;
        $this->playbookFile = $playbookFile;
        $this->options = $options;
    }

    /**
     * @return string
     */
    public function getDir()
    {
        return $this->dir;
    }

    /**
     * @return string
     */
    public function getInventoryFile()
    {
        return $this->inventoryFile;
    }

    /**
     * @return string
     */
    public function getPlaybookFile()
    {
        return $this->playbookFile;
    }

    /**
     * Play playbook
     */
    public function play()
    {
        $processBuilder = ProcessBuilder::create(["ansible-playbook", "-i", $this->inventoryFile, $this->playbookFile])
            ->setWorkingDirectory($this->dir)
            ->setTimeout(600)
            ->setEnv('ANSIBLE_FORCE_COLOR', true);


        if (!empty($this->options['pipelining'])) {
            $processBuilder->setEnv('ANSIBLE_SSH_PIPELINING', true);
        }

        return $processBuilder->getProcess();
    }
}
