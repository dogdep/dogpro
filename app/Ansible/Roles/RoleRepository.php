<?php namespace App\Ansible\Roles;

use App\Traits\ManageFilesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

/**
 * Class RoleRepository
 */
class RoleRepository
{
    use ManageFilesystem;

    /**
     * @var
     */
    private $path;

    /**
     * @param string|null $path
     */
    public function __construct($path = null)
    {
        if (is_null($path)) {
            $path = base_path("resources/ansible/roles");
        }

        $this->path = $path;
    }

    /**
     * @return array
     */
    public function all()
    {
        $roles = [];

        foreach (Finder::create()->in($this->path)->directories()->depth(0) as $dir) {
            $roles[basename($dir)] = $this->metadata(basename($dir));
        }

        return $roles;
    }

    /**
     * @param string $role
     * @return array
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function metadata($role)
    {
        $path = "{$this->path}/$role";

        $defaults = [];
        if ($this->fs()->exists("$path/defaults/main.yml")) {
            $defaults = Yaml::parse($this->fs()->get("$path/defaults/main.yml"));
        }

        $meta = ['variables'=>[]];
        if ($this->fs()->exists("$path/meta/main.yml")) {
            $meta = Yaml::parse($this->fs()->get("$path/meta/main.yml"));
            if (!isset($meta['variables'])) {
                $meta['variables'] = [];
            }
        }

        foreach ($meta['variables'] as $variable => $config) {
            $default = isset($defaults[$variable]) ? $defaults[$variable] : null;
            $meta['variables'][$variable] = [
                'name' => array_get($config, 'name', $variable),
                'desc' => array_get($config, 'desc'),
                'type' => is_array($default) ? 'array' : 'string',
                'default' => $default,
            ];
        }

        return $meta;
    }
}
