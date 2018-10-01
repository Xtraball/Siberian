<?php

namespace rock\sanitize\rules;


class Numbers extends Rule
{
    /**
     * @inheritdoc
     */
    public function sanitize($input)
    {
        return is_string($input) ? filter_var($input, FILTER_SANITIZE_NUMBER_INT) : $input;
    }
} 