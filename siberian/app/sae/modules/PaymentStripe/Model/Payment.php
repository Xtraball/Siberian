<?php

namespace PaymentStripe\Model;

use Core\Model\Base;
use Siberian\Exception;

/**
 * Class Payment
 * @package PaymentStripe\Model
 */
class Payment extends Base
{
    /**
     * @param $editorTree
     * @return mixed
     * @throws Exception
     */
    public static function editorNav($editorTree)
    {
        $currentUrl = str_replace(self::getBaseUrl(), "", self::getCurrentUrl());
        $editorTree["settings"]["childs"]["stripe"] = [
            "hasChilds" => false,
            "isVisible" => self::_canAccess("payment_stripe_settings"),
            "label" => p__("payment_stripe", "Stripe"),
            "icon" => "icofont icofont-stripe",
            "url" => self::_getUrl("paymentstripe/settings"),
            "is_current" => ("/paymentstripe/settings" === $currentUrl),
        ];

        return $editorTree;
    }

    /**
     * @param $acl
     * @return bool
     */
    protected static function _canAccess($acl)
    {
        $aclList = \Admin_Controller_Default::_getAcl();
        if ($aclList) {
            return $aclList->isAllowed($acl);
        }

        return true;
    }

    /**
     * @param string $url
     * @param array $params
     * @param null $locale
     * @return array|mixed|string
     */
    public static function _getUrl($url = "", array $params = [], $locale = null)
    {
        return __url($url);
    }
}