<?php namespace App\Exceptions;

use App\Ansible\Ansible;
use App\Model\Release;
use Exception;

/**
 * Class AnsibleException
 */
class AnsibleException extends ReleaseException
{
    /**
     * @var Ansible
     */
    private $ansible;

    public function __construct(Release $release, Ansible $ansible, $message = "", Exception $previous = null)
    {
        $this->ansible = $ansible;
        $message = sprintf(
            "Error while running ansible (%s/%s): %s",
            $ansible->getPlaybookFile(),
            $ansible->getInventoryFile(),
            $message
        );
        parent::__construct($release, $message, $previous);
    }

    /**
     * @return Ansible
     */
    public function getAnsible()
    {
        return $this->ansible;
    }
}
