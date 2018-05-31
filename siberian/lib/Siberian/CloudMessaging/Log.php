<?php

namespace Siberian\CloudMessaging;

/**
 * Class Log
 * @package Siberian\CloudMessaging
 */
class Log
{
    /**
     * @var string
     */
    public $type;

    /**
     * Log constructor.
     * @param string $type
     */
    public function __construct($type = 'Default')
    {
        $this->type = $type;
    }

    /**
     * Logs a message.
     *
     * @param  $sMessage @type string The message.
     */
    public function log($sMessage)
    {
        printf("%s Siberian\CloudMessaging\%s[%d]: %s\n",
            $this->type, date('r'), getmypid(), trim($sMessage)
        );
    }
}
