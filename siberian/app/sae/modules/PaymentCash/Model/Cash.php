<?php

namespace PaymentCash\Model;

use PaymentMethod\Model\GatewayAbstract;

/**
 * Class Cash
 * @package PaymentStripe\Model
 */
class Cash extends GatewayAbstract
{
    /**
     * @var array
     */
    public static $paymentMethod = "cash";

    /**
     * @param null $appId
     * @return bool
     */
    public function isSetup($appId = null)
    {
        try {
            $settings = self::getSettings($appId);
            return filter_var($settings->isEnabled(), FILTER_VALIDATE_BOOLEAN);
        } catch (\Exception $e) {
            return false;
        }
    }
}