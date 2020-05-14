<?php

use Siberian\Translation;

$init = static function ($bootstrap) {
    $translationFile = path('/app/sae/modules/Media/resources/translations/default/media.po');
    Translation::registerExtractor('media', 'Media', $translationFile);
};

