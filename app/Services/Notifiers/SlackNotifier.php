<?php namespace App\Services\Notifiers;

use App\Model\Release;
use Gitonomy\Git\Commit;
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
        if ($this->channel($release) === false || !$release->repo->param('notify_failure')) {
            return;
        }

        $this->sendNotification($release, $this->channel($release), 'Release failed', [
            'text' => $error,
            'color' => 'danger',
        ]);
    }

    /**
     * @param Release $release
     */
    public function notifySuccess(Release $release)
    {
        if ($this->channel($release) === false || !$release->repo->param('notify_success')) {
            return;
        }

        $this->sendNotification($release, $this->channel($release), 'Release successful', [
            'color' => 'good',
        ]);
    }

    /**
     * @param Release $release
     * @return string|false
     */
    public function channel(Release $release)
    {
        return $release->repo->param('slack_channel', false);
    }

    /**
     * @param Release $release
     * @param $commit
     * @return array
     */
    protected function describeCommitFields(Release $release, Commit $commit)
    {
        return [
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
        ];
    }

    /**
     * @param Release $release
     * @param string $channel
     * @param string $title
     * @param array $message
     */
    protected function sendNotification(Release $release, $channel, $title, array $message)
    {
        $message = $this->slack->createMessage()->to($channel)->attach($message + [
            'fallback' => $title,
            'fields' => $this->describeCommitFields($release, $release->commit())
        ]);

        try {
            $this->slack->send($message);
        } catch (\Exception $e) {
            logger()->error("Error while notifying slack", ['exception' => $e]);
        }
    }
}
