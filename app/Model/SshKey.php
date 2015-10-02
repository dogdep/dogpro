<?php namespace App\Model;

use App\Traits\ManageFilesystem;
use Illuminate\Contracts\Support\Jsonable;
use Symfony\Component\Finder\Finder;

/**
 * Class SshKey
 */
class SshKey implements \JsonSerializable, Jsonable
{
    use ManageFilesystem;

    /**
     * @var
     */
    private $name;

    /**
     * @var string|null
     */
    private $content;

    /**
     * @param string $name
     * @param string|null $content
     */
    function __construct($name, $content = null)
    {
        $this->name = $name;
        $this->content = $content;
    }

    /**
     * @return static[]
     */
    public static function all()
    {
        $keys = [];

        $keys_path = storage_path('keys');
        foreach (Finder::create()->in($keys_path)->files() as $key) {
            $keys[] = SshKey::get(substr($key, strlen($keys_path) + 1));
        }

        return $keys;
    }

    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    function jsonSerialize()
    {
        return [
            'id'=>$this->name,
            'host'=>$this->host(),
        ];
    }

    /**
     * @return string
     */
    public function host()
    {
        $name = $this->name;

        if ($pos = strpos($name, '/')) {
            $name = substr($name, $pos + 1);
        }

        return preg_replace('/(-[0-9]+)$/', '', $name);
    }

    public function save()
    {
        return $this->fs()->put($this->path(), $this->content) > 0;
    }

    public function delete()
    {
        return $this->fs()->delete($this->path());
    }

    /**
     * @return string
     */
    public function path()
    {
        return storage_path("keys/{$this->name}");
    }

    /**
     * @param string $name
     * @return null|static
     */
    public static function get($name)
    {
        $path = storage_path("keys/$name");

        if (file_exists($path)) {
            return new static($name);
        }

        return null;
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }
}
