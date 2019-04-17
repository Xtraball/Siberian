<?php

use Siberian\Assets;
use Siberian\Exporter;

$init = function($bootstrap) {
    # Exporter
    Exporter::register("booking", "Booking_Model_Booking", [
        "all" => __("All data"),
        "safe" => __("Clean-up sensible data"),
    ]);

    Assets::registerScss([
        "/app/sae/modules/Booking/features/booking/scss/booking.scss"
    ]);
};

