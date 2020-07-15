<?php

/**
 * InAppPurchase
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.19.0
 */

use InAppPurchase\Model\InAppPurchase;
use Siberian\Hook;
use Siberian\Translation;

/**
 * @param $editorTree
 * @return mixed
 * @throws Zend_Exception
 */
function iapEditorNav ($editorTree) {
    return InAppPurchase::editorNav($editorTree);
}

$init = static function ($bootstrap) {
    Translation::registerExtractor(
        'iap',
        'InAppPurchase',
        path('app/sae/modules/InAppPurchase/resources/translations/default/in_app_purchase.po'));

    Hook::listen(
        'editor.left.menu.ready',
        'iap_method_nav',
        'iapEditorNav');

    InAppPurchase::enable();
};

