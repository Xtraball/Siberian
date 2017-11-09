<?php

/**
 * Class Siberian_Api
 *
 * @version 4.8.4
 *
 */

class Siberian_Api {

    /**
     * Simplified ACL Array
     *
     * @var array
     */
    public static $acl_keys = array(
        "application" => array(
            "create" => "Create",
            "update" => "Update",
            "add" => "Grant user",
            "remove" => "Revoke user",
        ),
        "user" => array(
            "exist" => "Exists",
            "authenticate" => "Authenticate",
            "create" => "Create",
            "update" => "Update",
            "forgotpassword" => "Forgot password",
        ),
        "backoffice" => array(
            "manifest" => "Rebuild manifest",
            "cleartmp" => "Clear temp",
            "clearcache" => "Clear cache",
            "clearlogs" => "Clear logs",
        ),
        "push" => array(
            "list" => "List available applications",
            "send" => "Send global push notifications",
        ),
    );

    /**
     * @var array
     */
    public static $protected_keys = array(
        "application" => "Applications",
        "user" => "Users",
        "backoffice" => "Backoffice options",
        "push" => "Push notifications",
    );

    /**
     * @var array
     */
    public static $keys = array();

    /**
     * @param $namespace
     * @param array $keys
     */
    public static function register($namespace, $title, $keys = array()) {
        if(!in_array($namespace, self::$protected_keys) && is_array($keys)) {
            self::$acl_keys[$namespace] = $keys;
            self::$keys[$namespace] = $title;
        }
    }

    /**
     * @return array
     */
    public static function getSections() {
        return array_merge(self::$protected_keys, self::$keys);
    }
}
