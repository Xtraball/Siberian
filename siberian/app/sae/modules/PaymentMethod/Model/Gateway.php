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
     * @var array
     */
    public static $usedMobileGateways = [];

    /**
     *
     */
    const PAY = 'pay';

    /**
     *
     */
    const AUTHORIZE = 'authorize';

    /**
     *
     */
    const SUBSCRIPTION = 'subscription';

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
     * @return GatewayAbstract|mixed
     * @throws Exception
     * @throws \Zend_Exception
     */
    public static function get ($code)
    {
        if (array_key_exists($code, self::$gateways)) {
            return new self::$gateways[$code]['class']();
        }
        throw new Exception(
            p__('payment_method', "This payment gateway doesn't exists, '%s'.", null, $code));
    }

    /**
     * @param $code
     */
    public static function use ($code)
    {
        self::$usedMobileGateways[] = $code;
        self::$usedMobileGateways = array_unique(self::$usedMobileGateways);
    }

    /**
     * @param $editorTree
     * @return mixed
     * @throws \Zend_Exception
     */
    public static function editorNav($editorTree)
    {
        $currentUrl = str_replace((new self())->getBaseUrl(), "", (new self())->getCurrentUrl());

        $childs = [];
        $accessAny = [];
        foreach (self::$gateways as $code => $gateway) {
            // If no module is using the gateway, we don't display it
            if (!in_array($code, self::$usedMobileGateways, true)) {
                continue;
            }

            $childs[$code] = [
                'hasChilds' => false,
                'isVisible' => self::_canAccess($gateway['aclCode']),
                'label' => $gateway['label'],
                'icon' => $gateway['icon'],
                'url' => self::_getUrl($gateway['url']),
                'is_current' => ('/' . $gateway['url'] === $currentUrl),
            ];

            $accessAny[] = $gateway['aclCode'];
        }

        $editorTree['payment_gateways']['childs'] = $childs;
        $editorTree['payment_gateways']['isVisible'] = (new self())->_canAccessAnyOf($accessAny);

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
        return false;
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

    /**
     * @param string $url
     * @param array $params
     * @param null $locale
     * @return array|mixed|string
     * @throws \Zend_Controller_Router_Exception
     */
    public static function _getUrl($url = '', array $params = [], $locale = null)
    {
        return __url($url);
    }
}
