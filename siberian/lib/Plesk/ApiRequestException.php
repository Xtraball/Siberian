<?php
namespace Plesk;

class ApiRequestException extends \Exception
{
    /**
     * ApiRequestException constructor.
     * @param string $errorNode
     * @param int $code
     */
    public function __construct($errorNode, $code = 0)
    {
        if (is_string($errorNode)) {
            $message = $errorNode;
        } else {
            $message = isset($errorNode->errtext) ? (string)$errorNode->errtext : '';
            $code = isset($errorNode->errcode) ? (int)$errorNode->errcode : '';
        }

        parent::__construct($message, $code);
    }
}
