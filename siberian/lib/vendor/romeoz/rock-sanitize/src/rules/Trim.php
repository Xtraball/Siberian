<?php

namespace rock\sanitize\rules;


class Trim extends Rule
{
    /**
     * @inheritdoc
     */
    public function sanitize($input)
    {
        return is_string($input) ? trim($input) : $input;
    }
} 