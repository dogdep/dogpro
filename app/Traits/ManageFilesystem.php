<?php namespace App\Traits;

use Illuminate\Filesystem\Filesystem;

/**
 * Trait UsesFilesystem
 */
trait ManageFilesystem
{
    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @return Filesystem
     */
    protected function fs()
    {
        if (is_null($this->fs)) {
            $this->fs = new Filesystem();
        }

        return $this->fs;
    }
}
