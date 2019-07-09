<?php

use Siberian\Assets;

$init = function($bootstrap) {
    Siberian_Cache_Design::overrideCoreDesign("Cms");

    Assets::registerScss([
        "/app/sae/modules/Cms/features/custom_page/scss/custom_page.scss"
    ]);
};
