<?php

use Siberian\Assets;
use Siberian\Security;

$init = function($bootstrap) {
    Siberian_Cache_Design::overrideCoreDesign("Cms");

    Assets::registerScss([
        "/app/sae/modules/Cms/features/custom_page/scss/custom_page.scss"
    ]);

    Security::whitelistRoute("cms/application_page/editpostv2");
};
