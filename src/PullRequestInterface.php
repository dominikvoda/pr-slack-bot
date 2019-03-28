<?php declare(strict_types = 1);

namespace BE\PRSlackBot;

interface PullRequestInterface
{
    public function getPullRequestHtmlUrl(): string;


    public function getSlackUser(): string;


    public function getSlackChannel(): string;


    public function getSlackMessageId(): string;
}
