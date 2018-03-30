<?php

namespace Vnn\WpApiClient\Endpoint;

/**
 * Class Categories
 * @package Vnn\WpApiClient\Endpoint
 */
class Categories extends AbstractWpEndpoint
{
    /**
     * {@inheritdoc}
     */
    protected function getEndpoint()
    {
        return '/wp-json/wp/v2/categories';
    }
}
