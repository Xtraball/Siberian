<?php

namespace rock\sanitize\rules;

class Round extends Rule
{
    protected $precision = 0;

    public function __construct($precision = 0, $config = [])
    {
        $this->parentConstruct($config);
        $this->precision = $precision;
    }

    /**
     * @inheritdoc
     */
    public function sanitize($input)
    {
        return round($input, $this->precision);
    }
} 