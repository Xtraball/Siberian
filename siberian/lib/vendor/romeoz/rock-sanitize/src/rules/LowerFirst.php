<?php

namespace rock\sanitize\rules;


class LowerFirst extends Rule
{
    /**
     * @inheritdoc
     */
    public function sanitize($input)
    {
        return is_string($input) ? \rock\helpers\StringHelper::lowerFirst($input) : $input;
    }
} 