<?php

/**
 * InAppPurchase
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.19.0
 */

use InAppPurchase\Model\InAppPurchase;
use InAppPurchase\Model\Settings;
use Siberian\Hook;
use Siberian\Hook\Source;
use Siberian\Translation;

class_alias('InAppPurchase\Model\InAppPurchase', 'InAppPurchase_Model_InAppPurchase');

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

    Source::addActionBeforeArchive(
        Source::TYPE_ANDROID,
        'iap_add_billing_key',
        'InAppPurchase',
        static function (Application_Model_Device_Abstract $applicationDevice) {
            $billingKey = Settings::getKeyForAppId($applicationDevice->app->getId());
            $billingKey = trim($billingKey);
            if (!empty($billingKey)) {
                // Get billing_key_param.xml file
                $replacements = ["#BILLING_KEY" => $billingKey];

                $applicationDevice->__replace($replacements, $applicationDevice->_dest_source_res . '/values/billing_key_param.xml');
            }
        });

    //InAppPurchase::enable();
};

