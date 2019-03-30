<?php declare(strict_types = 1);

namespace BE\PRSlackBot\GitHub;

class GitHubWebhookProcessor
{
    private const EVENT_TYPE_PULL_REQUEST = 'pull_request';
    private const EVENT_TYPE_PULL_REQUEST_REVIEW = 'pull_request_review';

    /**
     * @var PullRequestEventHandler
     */
    private $pullRequestEventHandler;

    /**
     * @var PullRequestReviewEventHandler
     */
    private $pullRequestReviewEventHandler;


    public function __construct(
        PullRequestEventHandler $pullRequestEventHandler,
        PullRequestReviewEventHandler $pullRequestReviewEventHandler
    ) {
        $this->pullRequestEventHandler = $pullRequestEventHandler;
        $this->pullRequestReviewEventHandler = $pullRequestReviewEventHandler;
    }


    /**
     * @param mixed[] $requestBody
     */
    public function process(string $eventType, array $requestBody): void
    {
        if ($eventType === self::EVENT_TYPE_PULL_REQUEST) {
            $this->pullRequestEventHandler->handle($requestBody);
        }

        if ($eventType === self::EVENT_TYPE_PULL_REQUEST_REVIEW) {
            $this->pullRequestReviewEventHandler->handle($requestBody);
        }
    }
}
