<?php declare(strict_types = 1);

namespace BE\PRSlackBot;

use function array_rand;

class GitHubWebhookProcessor
{
    private const EVENT_TYPE_PULL_REQUEST = 'pull_request';
    private const EVENT_TYPE_PULL_REQUEST_REVIEW = 'pull_request_review';

    /**
     * @var string[]
     */
    private static $icons = [
        'changes_requested' => ':x:',
        'commented'         => ':speech_balloon:',
        'approved'          => ':heavy_check_mark:',
    ];

    private static $mergeMemes = [
        'https://cdn-images-1.medium.com/max/1200/1*5WmdexD6wYR05e-X9puJYA.jpeg',
        'https://cdn-images-1.medium.com/max/1600/1*7sSR_gIb6qLa8NrKtQ-Hsw.png',
        'https://i.redd.it/dsdbmyoirso01.jpg',
        'https://media.giphy.com/media/hZj44bR9FVI3K/giphy-downsized.gif',
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
        if ($eventType === self::EVENT_TYPE_PULL_REQUEST_REVIEW && $requestBody['action'] === 'submitted') {
            $this->processSubmittedReview($requestBody);
        }

        if ($eventType === self::EVENT_TYPE_PULL_REQUEST && $requestBody['action'] === 'closed') {
            $this->processPullRequestClosed($requestBody);
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


    private function processPullRequestClosed(array $requestBody): void
    {
        $pullRequest = $this->pullRequestsStorage->findByHtmlUrl($requestBody['pull_request']['html_url']);

        if ($pullRequest === null) {
            return;
        }

        if ($requestBody['pull_request']['merged'] === true) {
            $message = ':aw_yeah: Merged by *' . $requestBody['pull_request']['merged_by']['login'] . '*';

            $memeKey = array_rand(self::$mergeMemes);

            $this->slackMessageSender->sendAttachment(
                $pullRequest->getSlackChannel(),
                $pullRequest->getSlackMessageId(),
                $message,
                self::$mergeMemes[$memeKey]
            );
        }
    }
}
