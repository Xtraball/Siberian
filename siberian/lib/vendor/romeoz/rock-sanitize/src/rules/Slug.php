<?php

namespace rock\sanitize\rules;

use rock\helpers\Inflector;

class Slug extends Rule
{
    protected $replacement = '-';
    protected $lowercase = true;

    public function __construct($replacement = '-', $lowercase = true, $config = [])
    {
        $this->parentConstruct($config);
        $this->replacement = $replacement;
        $this->lowercase = $lowercase;
    }

    /**
     * @inheritdoc
     */
    public function sanitize($input)
    {
        return is_string($input) ? Inflector::slug($input, $this->replacement, $this->lowercase) : $input;
    }
} 