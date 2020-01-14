<?php

use Siberian\Hook;
use Siberian\Assets;
use Siberian\Translation;

$init = static function ($bootstrap) {
    Assets::registerScss([
        '/app/sae/modules/Form2/features/form_v2/scss/form2.scss'
    ]);

    $translationFile = path('/app/sae/modules/Form2/resources/translations/default/form2.po');
    Translation::registerExtractor('form2', 'Form2', $translationFile);

    if (method_exists(Hook::class, 'register')) {
        Hook::register('form2.submit', [
            'customer_id',
            'application',
            'request',
            'value_id',
        ]);
        Hook::register('form2.submit.success', [
            'payload',
            'timestamp',
            'customer_id',
            'application',
            'request',
            'value_id',
        ]);
        Hook::register('form2.submit.error', [
            'customer_id',
            'application',
            'request',
            'value_id',
        ]);
    }
};

