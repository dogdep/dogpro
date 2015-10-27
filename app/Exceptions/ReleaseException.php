<?php namespace App\Exceptions;

use App\Model\Release;

/**
 * Class ReleaseException
 */
class ReleaseException extends Exception
{
    /**
     * @var Release
     */
    private $release;

    /**
     * @param Release $release
     * @param string $message
     */
    public function __construct(Release $release, $message = "")
    {
        parent::__construct($message, 0);
        $this->release = $release;
    }

    /**
     * @return Release
     */
    public function getRelease()
    {
        return $this->release;
    }
}
