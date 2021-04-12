<?php
/**
 * @deprecated 4.20.8 removed from install, disabled feature.
 *
 * @param $bootstrap
 */

$init = static function($bootstrap) {
    # Exporter
    Siberian_Exporter::register("facebook", "Social_Model_Facebook");
};

