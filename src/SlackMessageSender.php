<?php declare(strict_types = 1);

namespace BE\PRSlackBot;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class SlackMessageSender
{
    /**
     * @var string
     */
    private $slackBotAccessToken;

    /**
     * @var Client
     */
    private $client;


    public function __construct(string $slackBotAccessToken, Client $client)
    {
        $this->slackBotAccessToken = $slackBotAccessToken;
        $this->client = $client;
    }


    public function send(string $channel, string $messageId, string $message): void
    {
        $this->client->post(
            'https://slack.com/api/chat.postMessage',
            [
                RequestOptions::HEADERS => ['Authorization' => 'Bearer ' . $this->slackBotAccessToken],
                RequestOptions::JSON    => [
                    'text'      => $message,
                    'channel'   => $channel,
                    'thread_ts' => $messageId,
                ],
            ]
        );
    }
}