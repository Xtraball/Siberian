<?php

namespace rock\sanitize\rules;


class RemoveTags extends Rule
{
    /**
     * @inheritdoc
     */
    public function sanitize($input)
    {
        return is_string($input) ? filter_sanitize_string_polyfill($input) : $input;
    }
} 