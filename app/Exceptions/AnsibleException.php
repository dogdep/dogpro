<?php namespace App\Exceptions;

use App\Ansible\Ansible;
use App\Model\Release;

/**
 * Class AnsibleException
 */
class AnsibleException extends ReleaseException
{
    /**
     * @var Ansible
     */
    private $ansible;

    /**
     * @param Release $release
     * @param Ansible $ansible
     * @param string $message
     */
    public function __construct(Release $release, Ansible $ansible, $message = "")
    {
        $this->ansible = $ansible;
        $message = sprintf(
            "Error while running ansible (%s/%s): %s",
            $ansible->getPlaybookFile(),
            $ansible->getInventoryFile(),
            $message
        );
        parent::__construct($release, $message);
    }

    /**
     * @return Ansible
     */
    public function getAnsible()
    {
        return $this->ansible;
    }
}
