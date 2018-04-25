<?php
/**
 * @param $bootstrap
 */
$init = function ($bootstrap) {
    Siberian_Privacy::registerModule(
        'm_commerce',
        __('M-Commerce'),
        'mcommerce/gdpr.phtml');
};