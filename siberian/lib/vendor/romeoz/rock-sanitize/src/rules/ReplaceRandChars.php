<?php

namespace rock\sanitize\rules;


class ReplaceRandChars extends Rule
{
    protected $replaceTo = '*';

    public function __construct($replaceTo = '*', $config = [])
    {
        $this->parentConstruct($config);
        $this->replaceTo = $replaceTo;
    }

    /**
     * @inheritdoc
     */
    public function sanitize($input)
    {
        return is_string($input) ? \rock\helpers\StringHelper::replaceRandChars($input, $this->replaceTo) : $input;
    }
} 