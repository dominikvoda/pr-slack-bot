<?php declare(strict_types = 1);

namespace BE\PRSlackBot\GitHub;

use BE\PRSlackBot\PullRequestsStorageInterface;
use BE\PRSlackBot\Slack\SlackMessageSender;

class StatusEventHandler
{
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
        if ($requestBody['state'] === 'success') {
            $this->processSuccessState($requestBody);
        }

        if ($requestBody['state'] === 'failure') {
            $this->processFailureState($requestBody);
        }
    }


    /**
     * @param mixed[] $requestBody
     */
    private function processSuccessState(array $requestBody): void
    {
        $pullRequest = $this->pullRequestsStorage->findByHeadCommit($requestBody['sha']);

        if ($pullRequest === null || !$pullRequest->isSlackReady()) {
            return;
        }

        $message = '*' . $requestBody['context'] . ' passed* :bananadance:';

        $this->slackMessageSender->send($pullRequest->getSlackChannel(), $pullRequest->getSlackMessageId(), $message);
    }


    /**
     * @param mixed[] $requestBody
     */
    private function processFailureState(array $requestBody): void
    {
        $pullRequest = $this->pullRequestsStorage->findByHeadCommit($requestBody['sha']);

        if ($pullRequest === null || !$pullRequest->isSlackReady()) {
            return;
        }

        $message = '*' . $requestBody['context'] . ' failed* :man-facepalming:';

        $this->slackMessageSender->sendButtonAttachment(
            $pullRequest->getSlackChannel(),
            $pullRequest->getSlackMessageId(),
            $message,
            ':jenkins_ci: Check console',
            $requestBody['target_url'] . '/console'
        );
    }
}
