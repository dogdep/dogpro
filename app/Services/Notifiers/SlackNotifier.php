<?php namespace App\Services\Notifiers;

use App\Model\Release;
use Maknz\Slack\Client;

/**
 * Class SlackNotifier
 */
class SlackNotifier implements ReleaseNotifierInterface
{
    /**
     * @var Client
     */
    private $slack;

    /**
     * @param Client $slack
     */
    function __construct(Client $slack = null)
    {
        $this->slack = $slack ?: app('maknz.slack');
    }

    /**
     * @param Release $release
     * @param string $error
     */
    public function notifyFailure(Release $release, $error)
    {
        $channel = $this->channel($release);

        if (!$channel || !$release->repo->param('notify_failure')) {
            return;
        }

        $commit = $release->commit();
        $this->slack->createMessage()->to($channel)->attach([
            'fallback' => 'Release failed',
            'text' => $error,
            'color' => 'danger',
            'fields' => [
                [
                    'title' => 'Commit',
                    'value' => $commit->getShortHash(),
                    'short' => true,

                ],
                [
                    'title' => 'Message',
                    'value' => $commit->getShortMessage(),
                    'short' => true
                ],
                [
                    'title' => 'Author',
                    'value' => $commit->getAuthorName() . '(' . $commit->getAuthorEmail() . ')',
                ],
                [
                    'title' => 'Details',
                    'value' => $release->url(),
                ],

            ]
        ])->send("Release failed");
    }

    /**
     * @param Release $release
     */
    public function notifySuccess(Release $release)
    {
        $channel = $this->channel($release);

        if (!$channel || !$release->repo->param('notify_success')) {
            return;
        }

        $commit = $release->commit();
        $this->slack->createMessage()->to($channel)->attach([
            'fallback' => 'Release successful',
            'color' => 'good',
            'fields' => [
                [
                    'title' => 'Commit',
                    'value' => $commit->getShortHash(),
                    'short' => true,

                ],
                [
                    'title' => 'Message',
                    'value' => $commit->getShortMessage(),
                    'short' => true
                ],
                [
                    'title' => 'Author',
                    'value' => $commit->getAuthorName() . '(' . $commit->getAuthorEmail() . ')',
                ],
                [
                    'title' => 'Details',
                    'value' => $release->url(),
                ],

            ]
        ])->send("Release successful");
    }

    public function channel(Release $release)
    {
        return $release->repo->param('slack_channel');
    }
}
