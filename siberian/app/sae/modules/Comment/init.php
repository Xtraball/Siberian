<?php
/**
 * @param $bootstrap
 */
$init = function ($bootstrap) {
    Siberian_Privacy::registerModule(
        "fanwall",
        __("Fanwall"),
        "comment/gdpr.phtml");
};