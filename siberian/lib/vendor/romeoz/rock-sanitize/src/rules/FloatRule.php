<?php

namespace rock\sanitize\rules;


class FloatRule extends Rule
{
    /**
     * @inheritdoc
     */
    public function sanitize($input)
    {
        return (float)$input;
    }
} 