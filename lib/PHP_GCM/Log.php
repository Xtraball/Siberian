<?php

namespace PHP_GCM;

class Log
{
    /**
     * Logs a message.
     *
     * @param  $sMessage @type string The message.
     */
    public function log($sMessage)
    {
        printf("%s GcmPHP[%d]: %s\n",
            date('r'), getmypid(), trim($sMessage)
        );
    }
}
