<?php

namespace rock\sanitize\rules;


class SpecialChars extends Rule
{
    /**
     * @inheritdoc
     */
    public function sanitize($input)
    {
        return is_string($input) ? preg_replace('/[^\w\s]/i', '', $input) : $input;
    }
} 