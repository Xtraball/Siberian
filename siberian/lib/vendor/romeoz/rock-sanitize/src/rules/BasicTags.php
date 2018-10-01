<?php

namespace rock\sanitize\rules;

class BasicTags extends Rule
{
    protected $allowedTags = "<br><p><a><strong><b><i><em><img><blockquote><code><dd><dl><hr><h1><h2><h3><h4><h5><h6><label><ul><li><span><sub><sup>";

    public function __construct($allowedTags = null, $config = [])
    {
        $this->parentConstruct($config);
        if (!empty($allowedTags)) {
            $this->allowedTags = $allowedTags;
        }
    }

    /**
     * @inheritdoc
     */
    public function sanitize($input)
    {
        return is_string($input) ? strip_tags($input, $this->allowedTags) : $input;
    }
} 