<?php

namespace Siberian;

/**
 * Class \Siberian\Api
 *
 * @version 4.16.0
 * @author Xtraball SAS <dev@xtraball.com>
 */

class Api
{

    /**
     * Simplified ACL Array
     *
     * @var array
     */
    public static $acl_keys = [
        'application' => [
            'create' => 'Create',
            'update' => 'Update',
            'add' => 'Grant user',
            'remove' => 'Revoke user',
        ],
        'user' => [
            'exist' => 'Exists',
            'authenticate' => 'Authenticate',
            'create' => 'Create',
            'update' => 'Update',
            'forgotpassword' => 'Forgot password',
        ],
        'backoffice' => [
            'manifest' => 'Rebuild manifest',
            'cleartmp' => 'Clear temp',
            'clearcache' => 'Clear cache',
            'clearlogs' => 'Clear logs',
        ],
        'push' => [
            'list' => 'List available applications',
            'send' => 'Send global push notifications',
        ],
    ];

    /**
     * @var array
     */
    public static $protected_keys = [
        'application' => 'Applications',
        'user' => 'Users',
        'backoffice' => 'Backoffice options',
        'push' => 'Push notifications',
    ];

    /**
     * @var array
     */
    public static $keys = [];

    /**
     * @param $namespace
     * @param array $keys
     */
    public static function register($namespace, $title, $keys = [])
    {
        if (!in_array($namespace, self::$protected_keys) && is_array($keys)) {
            self::$acl_keys[$namespace] = $keys;
            self::$keys[$namespace] = $title;
        }
    }

    /**
     * @return array
     */
    public static function getSections()
    {
        return array_merge(self::$protected_keys, self::$keys);
    }
}
