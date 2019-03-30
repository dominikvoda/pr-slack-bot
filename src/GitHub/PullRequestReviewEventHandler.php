<?php declare(strict_types = 1);

namespace BE\PRSlackBot\GitHub;

use BE\PRSlackBot\PullRequestsStorageInterface;
use BE\PRSlackBot\Slack\SlackMessageSender;
use function sprintf;

class PullRequestReviewEventHandler
{
    /**
     * @var string[]
     */
    private static $icons = [
        'changes_requested' => ':x:',
        'commented'         => ':speech_balloon:',
        'approved'          => ':heavy_check_mark:',
    ];

    /**
     * @var PullRequestsStorageInterface
     */
    private $pullRequestsStorage;

    /**
     * @var SlackMessageSender
     */
    private $slackMessageSender;


    public function __construct(
        PullRequestsStorageInterface $pullRequestsStorage,
        SlackMessageSender $slackMessageSender
    ) {
        $this->pullRequestsStorage = $pullRequestsStorage;
        $this->slackMessageSender = $slackMessageSender;
    }


    /**
     * @param mixed[] $requestBody
     */
    public function handle(array $requestBody): void
    {
        if ($requestBody['action'] === 'submitted') {
            $this->processSubmittedReview($requestBody);
        }
    }


    /**
     * @param mixed[] $requestBody
     */
    private function processSubmittedReview(array $requestBody): void
    {
        $pullRequest = $this->pullRequestsStorage->findByHtmlUrl($requestBody['pull_request']['html_url']);

        if ($pullRequest === null || !$pullRequest->isSlackReady()) {
            return;
        }

        $icon = self::$icons[$requestBody['review']['state']];

        $message = sprintf(
            '%s %sfrom *%s*',
            $icon,
            $requestBody['review']['body'] . ' ',
            $requestBody['review']['user']['login']
        );

        $this->slackMessageSender->send(
            $pullRequest->getSlackChannel(),
            $pullRequest->getSlackMessageId(),
            $message
        );
    }
}
