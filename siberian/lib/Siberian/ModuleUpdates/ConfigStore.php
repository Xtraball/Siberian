<?php

namespace Siberian\ModuleUpdates;

/**
 * Store backed by the system_config table (__get / __set).
 */
class ConfigStore implements Store
{
    /**
     * @inheritdoc
     */
    public function get($key)
    {
        $value = __get($key);

        return ($value === null || $value === false || $value === '') ? null : (string) $value;
    }

    /**
     * @inheritdoc
     */
    public function set($key, $value)
    {
        __set($key, $value);
    }
}
