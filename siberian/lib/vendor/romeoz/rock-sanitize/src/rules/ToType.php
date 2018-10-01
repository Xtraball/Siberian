<?php

namespace rock\sanitize\rules;


use rock\helpers\Helper;

class ToType extends Rule
{
    /**
     * @inheritdoc
     */
    public function sanitize($input)
    {
        return Helper::toType($input);
    }
} 