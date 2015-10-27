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

    /**
     * @param string $path
     * @return bool
     */
    public function removeDir($path)
    {
        if ($this->fs()->isDirectory($path)) {
            return $this->fs()->deleteDirectory($path);
        }

        if ($this->fs()->isFile($path)) {
            return $this->fs()->delete($path);
        }

        return true;
    }
}
