<?php declare(strict_types = 1);

namespace BE\PRSlackBot;

interface PullRequestsStorageInterface
{
    public function findByHtmlUrl(string $htmlUrl): ?PullRequestInterface;


    public function createPullRequest(string $htmlUrl): PullRequestInterface;


    public function updatePullRequest(
        PullRequestInterface $pullRequest,
        string $slackChannel,
        string $slackUser,
        string $slackThreadId
    ): void;
}
