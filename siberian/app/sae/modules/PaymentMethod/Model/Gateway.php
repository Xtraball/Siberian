<?php

namespace PaymentMethod\Model;

use Core\Model\Base;
use Siberian\Exception;

/**
 * Class Gateway
 * @package PaymentMethod\Model
 */
class Gateway extends Base
{
    /**
     * @var array
     */
    public static $gateways = [];

    /**
     * @param $code
     * @param array $options
     * @throws Exception
     */
    public static function register ($code, $options = [])
    {
        if (array_key_exists($code, self::$gateways)) {
            throw new Exception(
                p__("payment_method", "This code is already used, '%s'.", null, $code));
        }

        // Checking required options keys
        if (!array_key_exists("class", $options)) {
            throw new Exception(
                p__("payment_method", "You must declare a class to reference your gateway."));
        }

        self::$gateways[$code] = $options;
    }

    /**
     * @return mixed
     */
    public static function all ()
    {
        return self::$gateways;
    }

    /**
     * @param $code
     * @return bool
     */
    public static function has ($code)
    {
        return array_key_exists($code, self::$gateways);
    }

    /**
     * @param $code
     * @return mixed
     * @throws Exception
     */
    public static function get ($code)
    {
        if (array_key_exists($code, self::$gateways)) {
            return new self::$gateways[$code]["class"]();
        }
        throw new Exception(
            p__("payment_method", "This payment gateway doesn't exists, '%s'.", null, $code));
    }

    /**
     * @param $editorTree
     * @return mixed
     * @throws Exception
     */
    public static function editorNav($editorTree)
    {
        $currentUrl = str_replace(self::getBaseUrl(), "", self::getCurrentUrl());

        $childs = [];
        $accessAny = [];
        foreach (self::$gateways as $code => $gateway) {
            $childs[$code] = [
                "hasChilds" => false,
                "isVisible" => self::_canAccess($gateway["aclCode"]),
                "label" => $gateway["label"],
                "icon" => $gateway["icon"],
                "url" => self::_getUrl($gateway["url"]),
                "is_current" => ("/" . $gateway["url"] === $currentUrl),
            ];

            $accessAny[] = $gateway["aclCode"];
        }

        $editorTree["payment_gateways"]["childs"] = $childs;
        $editorTree["payment_gateways"]["isVisible"] = self::_canAccessAnyOf($accessAny);

        return $editorTree;
    }

    /**
     * @param $resources
     * @param null $value_id
     * @return bool
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