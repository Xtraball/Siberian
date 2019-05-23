<?php

use Siberian\Exporter;

$init = function($bootstrap) {
    # Export/Import
    Exporter::register("weather", "Weather_Model_Weather");
};

