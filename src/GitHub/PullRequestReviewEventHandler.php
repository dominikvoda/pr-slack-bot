<?php declare(strict_types = 1);

namespace BE\PRSlackBot\GitHub;

use BE\PRSlackBot\PullRequestsStorageInterface;
use BE\PRSlackBot\Slack\SlackMessageSender;
use function sprintf;

class PullRequestReviewEventHandler
{
    private const CHANGES_REQUESTED = 'changes_requested';
    private const COMMENTED = 'commented';
    private const APPROVED = 'approved';

    /**
     * @var string[]
     */
    private static $icons = [
        self::CHANGES_REQUESTED => ':x:',
        self::COMMENTED         => ':speech_balloon:',
        self::APPROVED          => ':heavy_check_mark:',
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

        $state = $requestBody['review']['state'];
        $body = $requestBody['review']['body'];

        if ($state === self::COMMENTED && $body === '') {
            return;
        }

        $icon = self::$icons[$state];

        $message = sprintf(
            '%s %sfrom *%s*',
            $icon,
            $body . ' ',
            $requestBody['review']['user']['login']
        );

        $this->slackMessageSender->send(
            $pullRequest->getSlackChannel(),
            $pullRequest->getSlackMessageId(),
            $message
        );
    }
}
