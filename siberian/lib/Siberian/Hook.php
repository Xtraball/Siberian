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
     * @param $actionName
     * @param $name
     * @param $callback
     * @param null $priority
     */
    public static function listen ($actionName, $name, $callback, $priority = null)
    {
        if (!array_key_exists($actionName, self::$hooks)) {
            self::$hooks[$actionName] = [];
        }

        $newCallback = [
            'name' => $name,
            'priority' => $priority,
            'callback' => $callback
        ];

        self::$hooks[$actionName][] = $newCallback;
    }

    /**
     * @param $actionName
     * @param null $payload
     */
    public static function trigger ($actionName, $payload = null)
    {
        if (array_key_exists($actionName, self::$hooks)) {
            $actions = self::$hooks[$actionName];

            usort($actions, function ($item1, $item2) {
                if ($item1['priority'] == $item2['priority']) {
                    return 0;
                }
                return $item1['priority'] < $item2['priority'] ? -1 : 1;
            });

            foreach ($actions as $index => $action) {
                Logger::info('TriggerHook::' . $actionName . '[' . $index . '] > ' .
                    $action['name']);
                try {
                    $action['callback']($payload);
                } catch (\Exception $e) {
                    Logger::info('TriggerHook::' . $actionName . '[' . $index . '] > Failed ' .
                        $action['name'] . ' > Exception: ' . $e->getMessage());
                }
            }
        }
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