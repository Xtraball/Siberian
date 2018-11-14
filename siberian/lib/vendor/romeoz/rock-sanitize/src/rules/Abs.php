<?php

namespace rock\sanitize\rules;


class Abs extends Rule
{
    /**
     * @inheritdoc
     */
    public function sanitize($input)
    {
        return abs($input);
    }
} 