<?php

namespace Siberian\Service\Push;

require __DIR__ . '/../../../PHP_GCM/SenderFcm.php';

/**
 * Class Fcm
 */
class Fcm extends \PHP_GCM\SenderFcm
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