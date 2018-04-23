<?php
/**
 * Wordpress Module
 *
 * @param $bootstrap
 */
$init = function($bootstrap) {
    // Exporter!
    Siberian_Exporter::register('wordpress', 'Wordpress_Model_Wordpress');
};
