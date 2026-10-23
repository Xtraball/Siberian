<?php

namespace Siberian\Notification;

use GuzzleHttp\Client as Guzzle;

/**
 * Class SlackWebhook
 *
 * Minimal Slack incoming-webhook client. Replaces maknz/slack (abandoned, and
 * pinned to guzzle ^6) and posts the same payload it did, so Slack receives
 * byte-for-byte the same message.
 *
 * @package Siberian\Notification
 */
class SlackWebhook
{
    /**
     * @var string
     */
    protected $endpoint;

    /**
     * @var array
     */
    protected $defaults;

    /**
     * @var Guzzle
     */
    protected $guzzle;

    /**
     * @var string|null
     */
    protected $channel = null;

    /**
     * @param string $endpoint Webhook url
     * @param array $attributes channel, username, link_names, unfurl_links, unfurl_media, allow_markdown
     * @param Guzzle|null $guzzle
     */
    public function __construct($endpoint, array $attributes = [], Guzzle $guzzle = null)
    {
        $this->endpoint = $endpoint;
        $this->defaults = $attributes + [
            'channel' => null,
            'username' => null,
            'link_names' => false,
            'unfurl_links' => false,
            'unfurl_media' => true,
            'allow_markdown' => true,
        ];
        $this->guzzle = $guzzle ?: new Guzzle();
    }

    /**
     * Targets the next message at a channel, then resets.
     *
     * @param string $channel
     * @return $this
     */
    public function to($channel)
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * @param string $text
     * @return void
     */
    public function send($text)
    {
        $payload = [
            'text' => $text,
            'channel' => $this->channel ?? $this->defaults['channel'],
            'username' => $this->defaults['username'],
            'link_names' => $this->defaults['link_names'] ? 1 : 0,
            'unfurl_links' => (bool) $this->defaults['unfurl_links'],
            'unfurl_media' => (bool) $this->defaults['unfurl_media'],
            'mrkdwn' => (bool) $this->defaults['allow_markdown'],
            'attachments' => [],
        ];
        $this->channel = null;

        $this->guzzle->post($this->endpoint, ['body' => json_encode($payload, JSON_UNESCAPED_UNICODE)]);
    }
}
