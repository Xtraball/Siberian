<?php

namespace Siberian\Notification;

use Maknz\Slack\Client as SlackClient;

/**
 * Class Slack
 * @package Siberian\Notification
 */
class Slack
{
    /**
     * @var SlackClient
     */
    public $slack;

    /**
     * Slack constructor.
     */
    public function __construct()
    {
        $channel = __get('fw_slack_channel');
        $username = __get('fw_slack_username');
        $webhook = __get('fw_slack_webhook');

        $settings = [
            'username' => $username,
            'channel' => $channel,
            'link_names' => true
        ];
        $this->slack = new SlackClient($webhook, $settings);
    }

    /**
     * @return SlackClient
     */
    public function getClient()
    {
        return $this->slack;
    }

    /**
     * @param $message
     */
    public function send($message)
    {
        $this->slack->send($message);
    }

    /**
     * @param $channel
     * @param $message
     */
    public function sendToChannel($channel, $message)
    {
        $this->slack
            ->to($channel)
            ->send($message);
    }
}