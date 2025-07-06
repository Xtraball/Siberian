<?php

declare(strict_types=1);

namespace App\Next\Core\Utility\Engine;

use Exception;

class SchemaException extends Exception
{
    public function __construct(string $message = "", int $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function __toString(): string
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

    public static function fromErrorCode(int $code, string $message = ""): self
    {
        return new self($message, $code);
    }
}
