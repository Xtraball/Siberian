<?php

/**
 * @param $bootstrap
 */
$init = function ($bootstrap) {
    // Register places scss for dynamic rebuild in colors page!
    \Siberian_Assets::registerScss([
        "/app/sae/modules/Places/features/places/scss/places.scss"
    ]);
};