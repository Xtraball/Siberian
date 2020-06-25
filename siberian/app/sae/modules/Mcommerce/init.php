<?php

use Siberian\Assets;
use Siberian\Privacy;
use Siberian\Translation;

/**
 * @param $bootstrap
 */
$init = static function ($bootstrap) {
    Privacy::registerModule(
        'm_commerce',
        __('M-Commerce'),
        'mcommerce/gdpr.phtml');

    Assets::registerScss([
        '/app/sae/modules/Mcommerce/features/m_commerce/scss/mcommerce.scss'
    ]);

    $translationFile = path('/app/sae/modules/Mcommerce/resources/translations/default/m_commerce.po');
    Translation::registerExtractor('m_commerce', 'Mcommerce', $translationFile);
};
