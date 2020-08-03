<?php

namespace InAppPurchase\Model;

use Core\Model\Base;

/**
 * Class InAppPurchase
 * @package InAppPurchase\Model
 */
class InAppPurchase extends Base
{
    /**
     * @var array
     */
    public static $isUsed = false;

    /**
     * @param $code
     */
    public static function enable ()
    {
        self::$isUsed = true;
    }

    /**
     * @param $editorTree
     * @return mixed
     * @throws \Zend_Exception
     */
    public static function editorNav($editorTree)
    {
        if (!self::$isUsed) {
            return $editorTree;
        }

        $currentUrl = str_replace((new self())->getBaseUrl(), '', (new self())->getCurrentUrl());
        $editorTree['in_app_purchase'] = [
            'hasChilds' => true,
            'id' => 'iap_side_menu',
            'isVisible' => self::_canAccessAnyOf(['iap_products', 'iap_purchases']),
            'label' => p__('iap', 'In app purchases'),
            'icon' => 'fa fa-shopping-cart',
            'childs' => [
                'iap_products' => [
                    'hasChilds' => false,
                    'isVisible' => self::_canAccess('iap_products'),
                    'label' => p__('iap', 'Products'),
                    'icon' => 'fa fa-archive',
                    'url' => '/inapppurchase/settings/products',
                    'is_current' => (preg_match('#^/inapppurchase/settings/products#i', $currentUrl) === 1),
                ],
                'iap_purchases' => [
                    'hasChilds' => false,
                    'isVisible' => self::_canAccess('iap_purchases'),
                    'label' => p__('iap', 'Purchases'),
                    'icon' => 'fa fa-credit-card',
                    'url' => '/inapppurchase/settings/purchases',
                    'is_current' => (preg_match('#^/inapppurchase/settings/purchases#i', $currentUrl) === 1),
                ],
                'iap_settings' => [
                    'hasChilds' => false,
                    'isVisible' => self::_canAccess('iap_settings'),
                    'label' => p__('iap', 'Settings'),
                    'icon' => 'fa fa-cogs',
                    'url' => '/inapppurchase/settings/general',
                    'is_current' => (preg_match('#^/inapppurchase/settings/general#i', $currentUrl) === 1),
                ],
            ],
        ];

        return $editorTree;
    }

    /**
     * @param $resources
     * @return bool
     * @throws \Zend_Controller_Request_Exception
     */
    protected static function _canAccessAnyOf($resources)
    {
        foreach ($resources as $resource) {
            $allowed = self::_canAccess($resource);
            if ($allowed) {
                return true;
            }
        }
    }

    /**
     * @param $acl
     * @return bool
     * @throws \Zend_Controller_Request_Exception
     */
    protected static function _canAccess($acl): bool
    {
        $aclList = \Admin_Controller_Default::_sGetAcl();
        if ($aclList) {
            return $aclList->isAllowed($acl);
        }

        return true;
    }
}
