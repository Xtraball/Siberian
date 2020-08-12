<?php

$init = function($bootstrap) {
    # Exporter
    Siberian_Exporter::register("booking", "Booking_Model_Booking", [
        "all" => __("All data"),
        "safe" => __("Clean-up sensible data"),
    ]);

};

