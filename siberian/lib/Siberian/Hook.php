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
     * Track triggered hooks to ensure, they are triggered only once!
     *
     * @var array
     */
    public static $triggered = [];

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
            'name' => $name,
            'priority' => $priority,
            'callback' => $callback
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
        if (array_key_exists($actionName, self::$hooks)) {
            // Do not trigger more than once!
            if (in_array($actionName, self::$triggered)) {
                return $payload;
            }
            self::$triggered[] = $actionName;

            $actions = self::$hooks[$actionName];

            usort($actions, function ($item1, $item2) {
                if ($item1['priority'] == $item2['priority']) {
                    return 0;
                }
                return $item1['priority'] < $item2['priority'] ? -1 : 1;
            });

            foreach ($actions as $index => $action) {
                try {
                    return $action['callback']($payload);
                } catch (\Exception $e) {
                    Logger::info('TriggerHook::' . $actionName . '[' . $index . '] > Failed ' .
                        $action['name'] . ' > Exception: ' . $e->getMessage());
                }
            }
        }
        return $payload;
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