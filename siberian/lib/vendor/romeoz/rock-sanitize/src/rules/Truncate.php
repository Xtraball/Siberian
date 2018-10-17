<?php

namespace rock\sanitize\rules;


class Truncate extends Rule
{
    protected $length = 4;
    protected $suffix = '...';

    public function __construct($length = 4, $suffix = '...', $config = [])
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
        return \rock\helpers\StringHelper::truncate($input, $this->length, $this->suffix);
    }
} 