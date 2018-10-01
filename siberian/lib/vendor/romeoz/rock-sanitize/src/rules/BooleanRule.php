<?php

namespace rock\sanitize\rules;


class BooleanRule extends Rule
{
    /**
     * @inheritdoc
     */
    public function sanitize($input)
    {
        return (bool)$input;
    }
} 