<?php declare(strict_types = 1);

namespace BE\PRSlackBot;

class GitHubWebhookProcessor
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
        PullRequestsStorageInterface $pullRequestRepository,
        SlackMessageSender $slackMessageSender
    ) {
        $this->pullRequestsStorage = $pullRequestRepository;
        $this->slackMessageSender = $slackMessageSender;
    }


    /**
     * @param mixed[] $requestBody
     */
    public function process(array $requestBody): void
    {
        $pullRequest = $this->pullRequestsStorage->findByHtmlUrl($requestBody['pull_request']['html_url']);

        if ($pullRequest === null) {
            return;
        }

        if ($requestBody['action'] === 'submitted') {
            $this->processSubmittedReview($pullRequest, $requestBody);
        }
    }


    private function processSubmittedReview(PullRequestInterface $pullRequest, array $requestBody): void
    {
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
