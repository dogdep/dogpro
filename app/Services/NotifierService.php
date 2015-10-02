<?php namespace App\Services;

use App\Model\Release;

/**
 * Class NotifierService
 */
class NotifierService implements Notifiers\ReleaseNotifierInterface
{
    const NOTIFIER_TAG = 'release_notifier';

    /**
     * @param Release $release
     * @param string $error
     */
    public function notifyFailure(Release $release, $error)
    {
        foreach ($this->notifiers() as $notifier) {
            $notifier->notifyFailure($release, $error);
        }
    }

    /**
     * @param Release $release
     */
    public function notifySuccess(Release $release)
    {
        foreach ($this->notifiers() as $notifier) {
            $notifier->notifySuccess($release);
        }
    }

    /**
     * @return Notifiers\ReleaseNotifierInterface[]
     */
    private function notifiers()
    {
        return app()->tagged(self::NOTIFIER_TAG);
    }
}
