<?php

namespace Siberian;

/**
 * Class Hook
 * @package Siberian
 */
class Hook
{
    /**
     * @var array
     */
    public static $hooks = [];

    /**
     * @var array
     */
    public static $registry = [];

    /**
     * Adds a hook into the registry, for future usage
     *
     * @param $actionName
     * @param $payloadKeys
     * @throws Exception
     */
    public static function register ($actionName, $payloadKeys)
    {
        if (!array_key_exists($actionName, self::$registry)) {
            self::$registry[$actionName] = $payloadKeys;
        } else {
            throw new Exception(p__('hook', 'This action is already registered, please use another name.'));
        }
    }

    /**
     * @param $actionName
     * @param $name
     * @param $callback callback function must return the payload, altered or not!
     * @param null $priority
     */
    public static function listen ($actionName, $name, $callback, $priority = null)
    {
        if (!array_key_exists($actionName, self::$hooks)) {
            self::$hooks[$actionName] = [];
        }

        $newCallback = [
            "name" => $name,
            "priority" => $priority,
            "callback" => $callback
        ];

        self::$hooks[$actionName][] = $newCallback;
    }

    /**
     * Must return the payload altered or not!
     *
     * @param $actionName
     * @param null $payload
     * @return mixed
     */
    public static function trigger ($actionName, $payload = null)
    {
        $newPayload = $payload;
        if (array_key_exists($actionName, self::$hooks)) {
            $actions = self::$hooks[$actionName];

            usort($actions, function ($item1, $item2) {
                if ($item1["priority"] == $item2["priority"]) {
                    return 0;
                }
                return $item1["priority"] < $item2["priority"] ? -1 : 1;
            });

            foreach ($actions as $index => $action) {
                $payloadBeforeCatch = $newPayload;
                try {
                    // Payload is passed upon ALL listeners, with their respective priorities!
                    $newPayload = $action["callback"]($newPayload);
                } catch (\Exception $e) {
                    Logger::info("TriggerHook::" . $actionName . "[" . $index . "] > Failed " .
                        $action["name"] . " > Exception: " . $e->getMessage());

                    // If any error occurred, we restore the payload as per before the try!
                    $newPayload = $payloadBeforeCatch;
                }
            }
        }
        return $newPayload;
    }

    /**
     * @param $actionName
     * @return array|mixed
     */
    public static function getListening ($actionName)
    {
        if (array_key_exists($actionName, self::$hooks)) {
            return self::$hooks[$actionName];
        }
        return [];
    }
}