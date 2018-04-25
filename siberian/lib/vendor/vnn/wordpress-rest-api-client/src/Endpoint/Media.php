<?php

namespace Vnn\WpApiClient\Endpoint;

/**
 * Class Media
 * @package Vnn\WpApiClient\Endpoint
 */
class Media extends AbstractWpEndpoint
{
    /**
     * {@inheritdoc}
     */
    protected function getEndpoint()
    {
        return '/wp-json/wp/v2/media';
    }
}
