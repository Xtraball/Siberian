<?php

namespace rock\sanitize\rules;


class StringRule extends Rule
{
    /**
     * @inheritdoc
     */
    public function sanitize($input)
    {
        return (string)$input;
    }
} 