<?php namespace App\Traits;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Class Jsonable
 */
trait Jsonable
{
    /**
     * Convert the object to its JSON representation.
     *
     * @param  int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        if (!$this instanceof Arrayable) {
            throw new \RuntimeException(sprintf("Class %s must imlement Arrayable", get_class($this)));
        }

        return json_encode($this->toArray(), $options);
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
        if (!$this instanceof Arrayable) {
            throw new \RuntimeException(sprintf("Class %s must imlement Arrayable", get_class($this)));
        }

        return $this->toArray();
    }
}
