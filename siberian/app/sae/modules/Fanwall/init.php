<?php

use Siberian\Assets;
use Siberian\Privacy;
use Siberian\Translation;

class_alias("Fanwall\Model\Fanwall", "Fanwall_Model_Fanwall");

/**
 * @param $bootstrap
 */
$init = function ($bootstrap) {
    // Postponed GDPR extract.
    //Privacy::registerModule(
    //    "fanwall",
    //    __("Fanwall"),
    //    "fanwall/gdpr.phtml");

    Assets::registerScss([
        "/app/sae/modules/Fanwall/features/fanwall2/scss/fanwall.scss"
    ]);

    $translationFile = path('/app/sae/modules/Fanwall/resources/translations/default/fanwall.po');
    Translation::registerExtractor('fanwall', 'Fanwall', $translationFile);
};
