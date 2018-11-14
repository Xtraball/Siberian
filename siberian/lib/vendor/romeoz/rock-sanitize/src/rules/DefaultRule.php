<?php

namespace rock\sanitize\rules;


class DefaultRule extends Rule
{
    protected $default;

    public function __construct($default = null, $config = [])
    {
        $this->parentConstruct($config);
        $this->default = $default;
    }

    /**
     * @inheritdoc
     */
    public function sanitize($input)
    {
        if (empty($input)) {
            return $this->default;
        }
        return $input;
    }
} 