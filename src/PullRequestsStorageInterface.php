<?php declare(strict_types = 1);

namespace BE\PRSlackBot;

interface PullRequestsStorageInterface
{
    public function findByHtmlUrl(string $htmlUrl): ?PullRequestInterface;


    public function findByHeadCommit(string $headCommit): ?PullRequestInterface;


    public function createPullRequest(string $htmlUrl, ?string $headCommit): PullRequestInterface;


    public function updateHeadCommit(PullRequestInterface $pullRequest, string $headCommit): void;


    public function updateSlackData(
        PullRequestInterface $pullRequest,
        string $slackChannel,
        string $slackUser,
        string $slackThreadId
    ): void;
}
