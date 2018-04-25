<?php

namespace Vnn\WpApiClient\Endpoint;

/**
 * Class Posts
 * @package Vnn\WpApiClient\Endpoint
 */
class Posts extends AbstractWpEndpoint
{
    /**
     * {@inheritdoc}
     */
    protected function getEndpoint()
    {
        return '/wp-json/wp/v2/posts';
    }
}
