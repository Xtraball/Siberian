<?php

use Siberian\Translation;

/**
 * @param $bootstrap
 */
$init = static function ($bootstrap) {
    $translationFile = path('/app/sae/modules/Media/resources/translations/default/media.po');
    Translation::registerExtractor('media', 'Media', $translationFile);

    $keywordsFile = path('/languages/base/c_keywords.po');
    if (!is_file($keywordsFile)) {
        copy(path('/app/sae/modules/Media/resources/assets/c_keywords_base.po'), $keywordsFile);
    }
    Translation::registerExtractor('keywords', 'Media', $keywordsFile);
};

