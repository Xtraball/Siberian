<?php

use Siberian\Exporter;

/**
 * @param $bootstrap
 */
$init = static function ($bootstrap) {
    Exporter::register('loyalty', 'LoyaltyCard_Model_LoyaltyCard');
};
