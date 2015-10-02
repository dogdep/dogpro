<?php namespace App\Services\Notifiers;

use App\Model\Release;

/**
 * Interface ReleaseNotifier
 */
interface ReleaseNotifierInterface
{
    /**
     * @param Release $release
     * @param string $error
     */
    public function notifyFailure(Release $release, $error);

    /**
     * @param Release $release
     */
    public function notifySuccess(Release $release);
}
