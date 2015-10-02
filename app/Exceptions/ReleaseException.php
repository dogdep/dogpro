<?php namespace App\Exceptions;

use App\Model\Release;
use Exception;

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
     * @param Exception $previous
     */
    public function __construct(Release $release, $message = "", Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
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
