<?php

namespace Siberian\Service\Push;

use Siberian\CloudMessaging\Sender\Fcm as Sender;

/**
 * Class Fcm
 */
class Fcm extends Sender
{
    /**
     * Siberian_Service_Push_Fcm constructor.
     * @param string $key
     */
    public function __construct($key)
    {
        parent::__construct($key);
    }
}