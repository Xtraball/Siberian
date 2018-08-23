<?php

namespace Siberian\Notification;

/**
 * Class Slack
 * @package Siberian\Notification
 */
class Slack
{
    /**
     * @var \Maknz\Slack\Client
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
        $this->slack = new \Maknz\Slack\Client($webhook, $settings);
    }

    /**
     * @return \Maknz\Slack\Client
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
     * @param $message
     */
    public function sendToChannel($channel, $message)
    {
        $this->slack
            ->to($channel)
            ->send($message);
    }
}