<?php

namespace rock\sanitize\rules;


class Email extends Rule
{
    /**
     * @inheritdoc
     */
    public function sanitize($input)
    {
        return is_string($input) ? filter_var($input, FILTER_SANITIZE_EMAIL) : $input;
    }
} 