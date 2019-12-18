<?php

use Siberian\Translation;

$init = function($bootstrap) {
    # Exporter
    Siberian_Exporter::register('weblink_mono', 'Weblink_Model_Type_Mono');
    Siberian_Exporter::register('weblink_multi', 'Weblink_Model_Type_Multi');

    Translation::registerExtractor(
        'weblink',
        'Weblink',
        path('app/sae/modules/Weblink/resources/translations/default/module_weblink.po'));
};
