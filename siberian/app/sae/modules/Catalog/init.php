<?php

use Siberian\Assets;

$init = function($bootstrap) {
    Assets::registerScss([
        "/app/sae/modules/Catalog/features/catalog/scss/catalog.scss",
        "/app/sae/modules/Catalog/features/set_meal/scss/set_meal.scss"
    ]);
};

