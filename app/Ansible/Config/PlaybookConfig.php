<?php namespace App\Ansible\Config;

use Symfony\Component\Yaml\Yaml;

/**
 * Class PlaybookConfig
 */
class PlaybookConfig
{
    /**
     * @var PlayConfig[]
     */
    private $playbooks = [];

    public function get($role)
    {
        return isset($this->playbooks[$role]) ? $this->playbooks[$role] : null;
    }

    /**
     * @return string
     */
    public function render()
    {
        return Yaml::dump($this->toArray());
    }

    /**
     * @param PlayConfig $play
     */
    public function add(PlayConfig $play)
    {
        $this->playbooks[$play->name()] = $play;
    }

    /**
     * @param array $array
     */
    public function setVars(array $array)
    {
        foreach ($this->playbooks as $play) {
            $play->setVars($array);
        }
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $conf = [];

        foreach ($this->playbooks as $play) {
            $conf[] = $play->toArray();
        }

        return $conf;
    }
}
