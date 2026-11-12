<?php

namespace Siberian\ModuleUpdates;

/**
 * Failure of the module update flow. The error code is stable (the backoffice switches on it),
 * the message is safe to show to the administrator.
 */
class Exception extends \RuntimeException
{
    /**
     * @var string
     */
    private $errorCode;

    /**
     * @var array
     */
    private $extra;

    /**
     * @param string $errorCode
     * @param string $message
     * @param array $extra
     */
    public function __construct($errorCode, $message, array $extra = [])
    {
        parent::__construct($message);
        $this->errorCode = $errorCode;
        $this->extra = $extra;
    }

    /**
     * @return string
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * @return array
     */
    public function getExtra()
    {
        return $this->extra;
    }
}
