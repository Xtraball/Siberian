<?php

namespace rock\sanitize\rules;


class RtrimWords extends Rule
{
    protected $words = [];

    public function __construct(array $words, $config = [])
    {
        $this->parentConstruct($config);
        $this->words = $words;
    }

    /**
     * @inheritdoc
     */
    public function sanitize($input)
    {
        return is_string($input) ? \rock\helpers\StringHelper::rtrimWords($input, $this->words) : $input;
    }
} 