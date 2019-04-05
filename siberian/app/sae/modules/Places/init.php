<?php

use Siberian\Assets;

/**
 * @param $bootstrap
 */
$init = function ($bootstrap) {
    // Register places scss for dynamic rebuild in colors page!
    Assets::registerScss([
        "/app/sae/modules/Places/features/places/scss/places.scss"
    ]);
};