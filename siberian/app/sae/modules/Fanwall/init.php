<?php

use Siberian\Assets;
use Siberian\Privacy;
use Siberian\Translation;

/**
 * @param $bootstrap
 */
$init = function ($bootstrap) {
    Privacy::registerModule(
        "fanwall",
        __("Fanwall"),
        "fanwall/gdpr.phtml");

    Assets::registerScss([
        "/app/sae/modules/Fanwall/features/fanwall/scss/fanwall.scss"
    ]);

    $translationFile = path("/app/sae/modules/Fanwall/resources/translations/default/fanwall.po");
    Translation::registerExtractor("fanwall", "Fanwall", $translationFile);
};