<?php

use Siberian\Privacy;
use Siberian\Translation;

/**
 * @param $bootstrap
 */
$init = function ($bootstrap) {
    Privacy::registerModule(
        "m_commerce",
        __("M-Commerce"),
        "mcommerce/gdpr.phtml");

    $translationFile = path("/app/sae/modules/Mcommerce/resources/translations/default/m_commerce.po");
    Translation::registerExtractor("m_commerce", "Mcommerce", $translationFile);
};