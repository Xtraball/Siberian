<?php

namespace rock\sanitize\rules;


class Lowercase extends Rule
{
    /**
     * @inheritdoc
     */
    public function sanitize($input)
    {
        return is_string($input) ? \rock\helpers\StringHelper::lower($input) : $input;
    }
} 