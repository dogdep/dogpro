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
    public function __construct(Client $slack = null)
    {
        $this->slack = $slack ?: app('maknz.slack');
    }

    /**
     * @param Release $release
     * @param string $error
     */
    public function notifyFailure(Release $release, $error)
    {
        if (!$this->channel($release) || !$release->repo->param('notify_failure')) {
            return;
        }

        $this->sendNotification($release, 'Release failed', [
            'text' => $error,
            'color' => 'danger',
        ]);
    }

    /**
     * @param Release $release
     */
    public function notifySuccess(Release $release)
    {
        if (!$this->channel($release) || !$release->repo->param('notify_success')) {
            return;
        }

        $this->sendNotification($release, 'Release successful', [
            'color' => 'good',
        ]);
    }

    /**
     * @param Release $release
     * @return null|false
     */
    public function channel(Release $release)
    {
        return $release->repo->param('slack_channel', false);
    }

    /**
     * @param Release $release
     * @param $title
     * @param array $message
     */
    protected function sendNotification(Release $release, $title, array $message)
    {
        $commit = $release->commit();
        $this->slack->createMessage()->to($this->channel($release))->attach($message + [
                'fallback' => $title,
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
            ]
        )->send($message);
    }
}
