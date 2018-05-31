<?php

namespace Siberian\Service\Push;

use Siberian\CloudMessaging\Sender\Gcm as Sender;

/**
 * Class Siberian_Service_Push_Gcm
 */
class Gcm extends Sender
{
    /**
     * Siberian_Service_Push_Gcm constructor.
     * @param string $key
     */
    public function __construct($key)
    {
        parent::__construct($key);
    }
}