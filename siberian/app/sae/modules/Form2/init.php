<?php

use Siberian\Assets;
use Siberian\Translation;

$init = static function ($bootstrap) {
    Assets::registerScss([
        '/app/sae/modules/Form2/features/form_v2/scss/form2.scss'
    ]);

    $translationFile = path('/app/sae/modules/Form2/resources/translations/default/form2.po');
    Translation::registerExtractor('form2', 'Form2', $translationFile);
};

