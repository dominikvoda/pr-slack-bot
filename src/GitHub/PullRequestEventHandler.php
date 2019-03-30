<?php declare(strict_types = 1);

namespace BE\PRSlackBot\GitHub;

use BE\PRSlackBot\PullRequestsStorageInterface;
use BE\PRSlackBot\Slack\SlackMessageSender;

class PullRequestEventHandler
{
    /**
     * @var PullRequestsStorageInterface
     */
    private $pullRequestsStorage;

    /**
     * @var SlackMessageSender
     */
    private $slackMessageSender;

    /**
     * @var MemesStorageInterface
     */
    private $memesStorage;


    public function __construct(
        PullRequestsStorageInterface $pullRequestsStorage,
        MemesStorageInterface $memesStorage,
        SlackMessageSender $slackMessageSender
    ) {
        $this->pullRequestsStorage = $pullRequestsStorage;
        $this->slackMessageSender = $slackMessageSender;
        $this->memesStorage = $memesStorage;
    }


    /**
     * @param mixed[] $requestBody
     */
    public function handle(array $requestBody): void
    {
        if ($requestBody['action'] === 'opened') {
            $this->processPullRequestOpened($requestBody);
        }

        if ($requestBody['action'] === 'synchronize') {
            $this->processPullRequestSynchronize($requestBody);
        }

        if ($requestBody['action'] === 'closed') {
            $this->processPullRequestClosed($requestBody);
        }
    }


    /**
     * @param mixed[] $requestBody
     */
    private function processPullRequestOpened(array $requestBody): void
    {
        $this->updatePullRequestHeadCommit(
            $requestBody['pull_request']['html_url'],
            $requestBody['pull_request']['head']['sha']
        );
    }


    /**
     * @param mixed[] $requestBody
     */
    private function processPullRequestSynchronize(array $requestBody): void
    {
        $this->updatePullRequestHeadCommit(
            $requestBody['pull_request']['html_url'],
            $requestBody['pull_request']['head']['sha']
        );
    }


    /**
     * @param mixed[] $requestBody
     */
    private function processPullRequestClosed(array $requestBody): void
    {
        $pullRequest = $this->pullRequestsStorage->findByHtmlUrl($requestBody['pull_request']['html_url']);

        if ($pullRequest === null || !$pullRequest->isSlackReady()) {
            return;
        }

        if ($requestBody['pull_request']['merged'] === true) {
            $message = ':aw_yeah: Merged by *' . $requestBody['pull_request']['merged_by']['login'] . '*';

            $this->slackMessageSender->sendAttachment(
                $pullRequest->getSlackChannel(),
                $pullRequest->getSlackMessageId(),
                $message,
                $this->memesStorage->getMergedMeme()
            );
        }
    }


    private function updatePullRequestHeadCommit(string $htmlUrl, string $headCommit): void
    {
        $pullRequest = $this->pullRequestsStorage->findByHtmlUrl($htmlUrl);

        if ($pullRequest === null || !$pullRequest->isSlackReady()) {
            $pullRequest = $this->pullRequestsStorage->createPullRequest(
                $htmlUrl,
                $headCommit
            );
        }

        $this->pullRequestsStorage->updateHeadCommit($pullRequest, $headCommit);
    }
}
