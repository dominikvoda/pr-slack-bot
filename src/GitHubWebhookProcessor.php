<?php declare(strict_types = 1);

namespace BE\PRSlackBot;

class GitHubWebhookProcessor
{
    private const EVENT_TYPE_PULL_REQUEST = 'pull_request';

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
        PullRequestsStorageInterface $pullRequestRepository,
        SlackMessageSender $slackMessageSender
    ) {
        $this->pullRequestsStorage = $pullRequestRepository;
        $this->slackMessageSender = $slackMessageSender;
    }


    /**
     * @param mixed[] $requestBody
     */
    public function process(string $eventType, array $requestBody): void
    {
        if ($eventType === self::EVENT_TYPE_PULL_REQUEST && $requestBody['action'] === 'submitted') {
            $this->processSubmittedReview($requestBody);
        }
    }


    private function processSubmittedReview(array $requestBody): void
    {
        $pullRequest = $this->pullRequestsStorage->findByHtmlUrl($requestBody['pull_request']['html_url']);

        if ($pullRequest === null) {
            return;
        }

        $icon = self::$icons[$requestBody['review']['state']];

        $message = sprintf(
            '%s %sfrom %s',
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
