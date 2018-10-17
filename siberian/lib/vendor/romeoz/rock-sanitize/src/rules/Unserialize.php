<?php

namespace rock\sanitize\rules;


use rock\helpers\Serialize;

class Unserialize extends Rule
{
    /**
     * @inheritdoc
     */
    public function sanitize($input)
    {
        if (!is_string($input)) {
            return $input;
        }
        return Serialize::unserialize($input, false);
    }
} 