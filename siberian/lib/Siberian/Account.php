<?php

namespace Siberian;

/**
 * Class Account
 * @package Siberian
 */
class Account
{
    /**
     * @var array
     */
    public static $extendedFields = [];

    /**
     * @var array
     */
    public static $callbackSaveHandlers = [];

    /**
     * @var array
     */
    public static $callbackPopulateHandlers = [];

    /**
     * @param $moduleKey
     * @param $fields
     * @param $callbackPopulate
     * @param $callbackSave
     * @throws Exception
     */
    public static function addFields ($moduleKey, $fields, $callbackPopulate, $callbackSave)
    {
        if (array_key_exists($moduleKey, self::$extendedFields)) {
            throw new Exception(p__("siberian_account",
                "This moduleKey is already used, please choose another one!"));
        }

        // Adds automatically the moduleKey
        foreach ($fields as &$field) {
            $field["_moduleKey"] = $moduleKey;
        }
        unset($field);
        self::$extendedFields[$moduleKey] = $fields;
        self::$callbackPopulateHandlers[$moduleKey] = $callbackPopulate;
        self::$callbackSaveHandlers[$moduleKey] = $callbackSave;
    }

    /**
     * Returns field populate!
     *
     * @param $context
     * @return array|mixed
     */
    public static function getFields ($context)
    {
        $fields = [];
        foreach (self::$extendedFields as $_moduleKey => $_fields) {
            $callbackPopulate = self::$callbackPopulateHandlers[$_moduleKey];
            $fields[$_moduleKey] = call_user_func_array($callbackPopulate, [$context, $_fields]);
        }

        return $fields;
    }

    /**
     * @param $context
     * @param $extendedFields
     */
    public static function saveFields ($context, $extendedFields)
    {
        foreach ($extendedFields as $_moduleKey => $_fields) {
            $callbackSave = self::$callbackSaveHandlers[$_moduleKey];
            call_user_func_array($callbackSave, [$context, $_fields]);
        }
    }
}
