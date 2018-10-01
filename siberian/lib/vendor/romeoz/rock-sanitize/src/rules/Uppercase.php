<?php

namespace rock\sanitize\rules;


class Uppercase extends Rule
{
    /**
     * @inheritdoc
     */
    public function sanitize($input)
    {
        return is_string($input) ? \rock\helpers\StringHelper::upper($input) : $input;
    }
} 