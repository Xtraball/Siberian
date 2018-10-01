<?php

namespace rock\sanitize\rules;


class TruncateWords extends Rule
{
    protected $length = 100;
    protected $suffix = '...';

    public function __construct($length = 100, $suffix = '...', $config = [])
    {
        $this->parentConstruct($config);
        $this->length = $length;
        $this->suffix = $suffix;
    }

    /**
     * @inheritdoc
     */
    public function sanitize($input)
    {
        return \rock\helpers\StringHelper::truncateWords($input, $this->length, $this->suffix);
    }
} 