<?php namespace App\Ansible\Config;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;

/**
 * Class PlayConfig
 */
class PlayConfig implements Jsonable, JsonSerializable, Arrayable
{
    use \App\Traits\Jsonable;

    /**
     * @var array
     */
    private $hosts = [];

    /**
     * @var array
     */
    private $roles = [];

    /**
     * @var array
     */
    private $vars = [];

    /**
     * @var string
     */
    private $name = null;

    /**
     * @var boolean
     */
    private $sudo = false;

    /**
     * @param string $name
     * @param array $hosts
     */
    public function __construct($name, $hosts = [])
    {
        $this->name = $name;
        $this->hosts = (array) $hosts;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param string $role
     * @param array $roleVars
     */
    public function addRole($role, array $roleVars = [])
    {
        if (!isset($this->roles[$role])) {
            $this->roles[$role] = ['role' => (string) $role] + $roleVars;
            return;
        }

        $this->roles[$role] = array_merge_recursive($roleVars, $this->roles[$role]);
    }

    /**
     * @param array $roles
     */
    public function setRoles(array $roles)
    {
        $this->roles = $roles;
    }

    /**
     * @return string
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * @param string $hosts
     */
    public function setHosts($hosts)
    {
        $this->hosts = (array) $hosts;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param array $vars
     */
    public function setVars(array $vars)
    {
        $this->vars = $vars;
    }

    /**
     * @param bool $sudo
     */
    public function setSudo($sudo)
    {
        $this->sudo = $sudo;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'name' => $this->name,
            'sudo' => $this->sudo ? 'yes' : 'no',
            'hosts' => implode(',', $this->hosts),
            'vars' => $this->vars,
            'roles' => array_values($this->roles),
        ];
    }
}
