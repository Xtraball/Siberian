<?php

namespace InAppPurchase\Model;

use Core\Model\Base;

/**
 * Class Settings
 * @package InAppPurchase\Model
 */
class Settings extends Base
{
    /**
     * @var string
     */
    protected $_db_table = Db\Table\Settings::class;

    /**
     * @param $appId
     * @return bool|string
     * @throws \Zend_Exception
     */
    public static function getKeyForAppId ($appId)
    {
        $result = (new self())->find($appId, 'app_id');
        if ($result && $result->getId()) {
            return $result->getGoogleBillingKey();
        }
        return false;
    }
}
