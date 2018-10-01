<?php

namespace rock\sanitize\rules;


use rock\helpers\NumericHelper;

class Negative extends Rule
{
    /**
     * @inheritdoc
     */
    public function sanitize($input)
    {
        $input = NumericHelper::toNumeric($input);
        return $input > 0 ? 0 : $input;
    }
} 