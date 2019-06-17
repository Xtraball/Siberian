<?php

use Siberian\Exporter;

$init = function($bootstrap) {
    # Exporter
    Exporter::register("discount", "Discount_Model_Discount");
};
