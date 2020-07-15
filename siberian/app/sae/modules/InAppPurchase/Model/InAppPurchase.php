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
            'hasChilds' => false,
            'isVisible' => self::_canAccess('in_app_purchase_admin'),
            'label' => p__('iap', 'In app purchases'),
            'icon' => 'fa fa-shopping-cart',
            'url' => '/inapppurchase/settings',
            'is_current' => (preg_match('#^\/inapppurchase\/settings#i', $currentUrl) === 1),
        ];

        return $editorTree;
    }

    /**
     * @param $resources
     * @return bool
     * @throws \Zend_Controller_Request_Exception
     */
    protected function _canAccessAnyOf($resources)
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
