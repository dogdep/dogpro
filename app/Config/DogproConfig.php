<?php namespace App\Config;

use App\Ansible\Config\PlayConfig;
use App\Exceptions\InvalidConfigException;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;
use Symfony\Component\Yaml\Yaml;

/**
 * Class DogproConfig
 */
class DogproConfig implements JsonSerializable, Jsonable, Arrayable
{
    use \App\Traits\Jsonable;

    const FILENAME = "dogpro.yml";

    /**
     * @var array
     */
    private $data = [];

    /**
     * @param string $content
     */
    public function __construct($content = null)
    {
        if (!is_null($content)) {
            $this->data = Yaml::parse($content);
        }
    }

    public function globals()
    {
        if (!isset($this->data['defaults'])) {
            return [];
        }

        return (array) $this->data['defaults'];
    }

    public static function defaults()
    {
        return [
            'deploy_dir' => '/var/www',
            'deploy_user' => 'www',
            'http_user' => 'www',
            'public_dir' => 'public',
        ];
    }

    /**
     * @return PlayConfig[]
     * @throws InvalidConfigException
     */
    public function roles()
    {
        if (!isset($this->data['plays'])) {
            return [];
        }

        $plays = [];
        foreach ($this->data['plays'] as $playConfig) {
            if (!isset($playConfig['role'])) {
                throw new InvalidConfigException("Role field must be defined for every play");
            }


            $play = new PlayConfig(array_get($playConfig, 'name', $playConfig['role']));
            $play->setHosts(array_get($playConfig, 'hosts', 'all'));
            if (isset($playConfig['vars'])) {
                $play->addRole($playConfig['role'], (array) $playConfig['vars']);
            } else {
                $play->addRole($playConfig['role'], (array) array_except($playConfig, [
                    'role', 'name', 'sudo', 'hosts', 'remote_user', 'tasks', 'handlers'
                ]));
            }
            $plays[] = $play;
        }

        return $plays;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'roles' => $this->roles(),
            'global' => $this->globals(),
        ];
    }
}
