<?php

namespace Siberian\ModuleUpdates;

/**
 * Small key/value settings store (backed by system_config in the application, by an array in tests).
 */
interface Store
{
    /**
     * @param string $key
     * @return string|null
     */
    public function get($key);

    /**
     * @param string $key
     * @param string $value
     */
    public function set($key, $value);
}
