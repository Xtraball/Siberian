<?php

namespace rock\sanitize\rules;


use rock\base\ObjectInterface;
use rock\base\ObjectTrait;

abstract class Rule implements ObjectInterface
{
    use ObjectTrait {
        ObjectTrait::__construct as parentConstruct;
    }

    public $recursive = true;

    /**
     * @param mixed $input
     * @return bool
     */
    abstract public function sanitize($input);
} 