<?php

use Siberian\Assets;
use Siberian\Exporter;

$init = static function ($bootstrap) {
    Exporter::register('radio', 'Radio_Model_Radio');
    Assets::registerScss([
        '/app/sae/modules/Radio/features/radio/scss/radio.scss'
    ]);
};
