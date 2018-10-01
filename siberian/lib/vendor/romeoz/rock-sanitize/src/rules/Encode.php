<?php

namespace rock\sanitize\rules;


class Encode extends Rule
{
    protected $doubleEncode = true;

    public function __construct($doubleEncode = true, $config = [])
    {
        $this->parentConstruct($config);
        $this->doubleEncode = $doubleEncode;
    }

    /**
     * @inheritdoc
     */
    public function sanitize($input)
    {
        return is_string($input) ? \rock\helpers\StringHelper::encode($input, $this->doubleEncode) : $input;
    }
} 