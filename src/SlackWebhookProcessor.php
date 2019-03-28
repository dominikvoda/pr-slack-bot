<?php declare(strict_types = 1);

namespace BE\PRSlackBot;

use Nette\Utils\Strings;

class SlackWebhookProcessor
{
    /**
     * @var PullRequestsStorageInterface
     */
    private $pullRequestsStorage;


    public function __construct(PullRequestsStorageInterface $pullRequestsStorage)
    {
        $this->pullRequestsStorage = $pullRequestsStorage;
    }


    /**
     * @param mixed[] $requestBody
     */
    public function process(array $requestBody): void
    {
        // skip shared links in threads
        if (isset($requestBody['event']['thread_ts'])) {
            return;
        }

        if ($requestBody['event']['type'] === 'link_shared') {
            $this->processLinkSharedEvent($requestBody);
        }
    }


    /**
     * @param mixed[] $requestBody
     */
    public function processLinkSharedEvent(array $requestBody): void
    {
        $link = $requestBody['event']['links'][0];

        // process only github pull request links
        if ($link['domain'] === 'github.com' && Strings::contains($link['url'], '/pull/')) {
            $pullRequest = $this->pullRequestsStorage->findByHtmlUrl($link['url']);

            if ($pullRequest === null) {
                $pullRequest = $this->pullRequestsStorage->createPullRequest($link['url']);
            }

            $this->pullRequestsStorage->updatePullRequest(
                $pullRequest,
                $requestBody['event']['channel'],
                $requestBody['event']['user'],
                $requestBody['event']['message_ts']
            );
        }
    }
}
