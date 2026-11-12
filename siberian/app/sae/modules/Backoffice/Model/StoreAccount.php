<?php

use Siberian\ModuleUpdates\ConfigStore;
use Siberian\ModuleUpdates\CurlTransport;
use Siberian\ModuleUpdates\Exception;
use Siberian\StoreAccount;

/**
 * Class Backoffice_Model_StoreAccount
 *
 * Glue between the application (settings, private cache folder) and the framework-free Siberian\StoreAccount.
 */
class Backoffice_Model_StoreAccount
{
    /**
     * @param string $label shown on the store when the customer approves, usually the host of this install
     * @return StoreAccount
     */
    public static function service($label = '')
    {
        return new StoreAccount(
            new CurlTransport(),
            new ConfigStore(),
            // Not under var/cache: "clear cache" and every module update empty it, and with the encryption key in it the
            // saved connection is lost. Siberian_Cache never touches var/private.
            Core_Model_Directory::getBasePathTo('var/private/store-account'),
            $label,
            null,
            // Only the configuration file can point somewhere else (staging, tests), never the database.
            __getConfig('store_account_base_url') ?: StoreAccount::BASE_URL,
            // Where it lived in 5.1.x before this change: a key and cache that survived there are moved over.
            Core_Model_Directory::getBasePathTo('var/cache/store-account')
        );
    }

    /**
     * @param Exception $e
     * @return array
     */
    public static function errorPayload(Exception $e)
    {
        return [
            'error' => true,
            'code' => $e->getErrorCode(),
            'message' => $e->getMessage(),
        ];
    }
}
