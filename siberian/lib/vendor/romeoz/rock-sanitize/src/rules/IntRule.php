<?php

namespace rock\sanitize\rules;


class IntRule extends Rule
{
    /**
     * @inheritdoc
     */
    public function sanitize($input)
    {
        return (int)$input;
    }
} 