<?php declare(strict_types = 1);

namespace BE\PRSlackBot\GitHub;

class GitHubWebhookProcessor
{
    private const EVENT_TYPE_PULL_REQUEST = 'pull_request';
    private const EVENT_TYPE_PULL_REQUEST_REVIEW = 'pull_request_review';
    private const EVENT_TYPE_STATUS = 'status';

    /**
     * @var PullRequestEventHandler
     */
    private $pullRequestEventHandler;

    /**
     * @var PullRequestReviewEventHandler
     */
    private $pullRequestReviewEventHandler;

    /**
     * @var StatusEventHandler
     */
    private $statusEventHandler;


    public function __construct(
        PullRequestEventHandler $pullRequestEventHandler,
        PullRequestReviewEventHandler $pullRequestReviewEventHandler,
        StatusEventHandler $statusEventHandler
    ) {
        $this->pullRequestEventHandler = $pullRequestEventHandler;
        $this->pullRequestReviewEventHandler = $pullRequestReviewEventHandler;
        $this->statusEventHandler = $statusEventHandler;
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
        if ($eventType === self::EVENT_TYPE_STATUS) {
            $this->statusEventHandler->handle($requestBody);
        }
    }
}
