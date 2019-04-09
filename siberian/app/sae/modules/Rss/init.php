<?php

use Siberian\Assets;
use Siberian\Exporter;

$init = function($bootstrap) {
    # Exporter
    Exporter::register("rss_feed", "Rss_Model_Feed");

    Assets::registerScss([
        "/app/sae/modules/Rss/features/rss/scss/rss.scss"
    ]);
};

