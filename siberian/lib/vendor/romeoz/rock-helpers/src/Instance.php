<?php

namespace rock\helpers;


use rock\base\ObjectInterface;

class Instance
{
    /**
     * Configure instance.
     * @param object $instance
     * @param array $config name-value pairs that will be used to initialize the object properties
     */
    public static function configure($instance, array $config = [])
    {
        foreach ($config as $name => $value) {
            if (is_callable($value) && is_array($value) && is_subclass_of($value[0], '\rock\base\Configure')) {
                $value = call_user_func($value, $instance);
            }
            $instance->$name = $value;
        }
    }

    /**
     * @param object|string|array|static $reference an object or a reference to the desired object.
     * @param string|null $defaultClass default name of class
     * @param array $args arguments of constructor.
     * @param bool $throwException
     * @return ObjectInterface
     * @throws InstanceException
     */
    public static function ensure($reference, $defaultClass = null, array $args = [], $throwException = true)
    {
        if (is_object($reference)) {
            return $reference;
        }
        if (isset($reference) && class_exists('\rock\di\Container')) {
            return \rock\di\Container::load($reference, $args, $throwException);
        } else {
            $config = [];
            if (is_array($reference)) {
                $config = $reference;
                if (!isset($defaultClass)) {
                    $defaultClass = $config['class'];
                }
                unset($config['class']);
            } elseif (is_string($reference) && !isset($defaultClass)) {
                $defaultClass = $reference;
            }

            if (!class_exists($defaultClass)) {
                if ($throwException) {
                    throw new InstanceException(InstanceException::UNKNOWN_CLASS, ['class' => Helper::getValue($defaultClass, 'null', true)]);
                }
                return null;
            }

            $reflect = new \ReflectionClass($defaultClass);
            $args = static::calculateArgs($reflect, $args, $config);
            return $reflect->newInstanceArgs($reflect->getConstructor() ? $args : []);
        }
    }

    protected static function calculateArgs(\ReflectionClass $reflect, array $args = [], array $config = [])
    {
        $constructor = $reflect->getConstructor();
        if ($constructor instanceof \ReflectionMethod && ($params = $constructor->getParameters())) {
            $i = 0;
            foreach ($params as $param) {
                if (!array_key_exists($i, $args) && $param->isDefaultValueAvailable()) {
                    $args[$i] = $param->getDefaultValue();
                }
                ++$i;
            }

            $last = end($params);
            $interfaces = array_flip($reflect->getInterfaceNames());
            if (isset($interfaces['rock\base\ObjectInterface']) && is_array($last->getDefaultValue())) {
                end($args);
                $args[key($args)] = $config;
                reset($args);
            }
        }

        return $args;
    }
}