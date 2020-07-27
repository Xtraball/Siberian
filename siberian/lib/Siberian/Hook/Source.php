<?php

namespace Siberian\Hook;

use Siberian\Exception;

/**
 * Class Sources
 * @package Siberian\Hook
 */
class Source
{
    /**
     * @var string
     */
    const TYPE_IOS = 'ios';

    /**
     * @var string
     */
    const TYPE_ANDROID = 'android';

    /**
     * @var array
     */
    public static $ios = [];

    /**
     * @var array
     */
    public static $android = [];

    /**
     * @param $type
     * @param $key
     * @param $module
     * @param $callback
     * @throws Exception
     * @throws \Zend_Exception
     */
    public static function addActionBeforeArchive ($type, $key, $module, $callback)
    {
        if (!in_array($type, [self::TYPE_IOS, self::TYPE_ANDROID])) {
            throw new Exception(p__('hook',
                'The given type %s is not supported, please use %s, or %s', $type, self::TYPE_IOS, self::TYPE_ANDROID));
        }

        if (!is_callable($callback)) {
            throw new Exception(p__('hook', 'The given callback is not callable.'));
        }

        if ($type === self::TYPE_IOS) {
            self::$ios[$key] = [
                'moduke' => $module,
                'callback' => $callback
            ];
        }

        if ($type === self::TYPE_ANDROID) {
            self::$android[$key] = [
                'moduke' => $module,
                'callback' => $callback
            ];
        }
    }

    /**
     * @param $type
     * @return array
     */
    public static function getActionsBeforeArchive ($type): array
    {
        return $type === self::TYPE_IOS ? self::$ios : self::$android;
    }
}
