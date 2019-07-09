<?php

use Siberian\Exporter;

$init = function($bootstrap) {
    # Exporter
    Exporter::register("twitter", "Twitter_Model_Twitter");
};

