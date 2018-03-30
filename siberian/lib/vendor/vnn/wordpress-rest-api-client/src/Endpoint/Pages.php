<?php

namespace Vnn\WpApiClient\Endpoint;

/**
 * Class Pages
 * @package Vnn\WpApiClient\Endpoint
 */
class Pages extends AbstractWpEndpoint
{
    /**
     * {@inheritdoc}
     */
    protected function getEndpoint()
    {
        return '/wp-json/wp/v2/pages';
    }
}
