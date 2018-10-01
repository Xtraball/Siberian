<?php

namespace rock\base;


/**
 * Interface ObjectInterface
 */
interface ObjectInterface
{

    public static function className();

    /**
     * Initializes the object.
     * This method is invoked at the end of the constructor after the object is initialized with the
     * given configuration.
     */
    public function init();

    /**
     * Returns a value indicating whether a property is defined.
     * A property is defined if:
     *
     * - the class has a getter or setter method associated with the specified name
     *   (in this case, property name is case-insensitive);
     * - the class has a member variable with the specified name (when `$checkVars` is true);
     *
     * @param string $name the property name
     * @param boolean $checkVars whether to treat member variables as properties
     * @return boolean whether the property is defined
     * @see canGetProperty
     * @see canSetProperty
     */
    public function hasProperty($name, $checkVars = true);

    /**
     * Returns a value indicating whether a property can be read.
     * A property is readable if:
     *
     * - the class has a getter method associated with the specified name
     *   (in this case, property name is case-insensitive);
     * - the class has a member variable with the specified name (when `$checkVars` is true);
     *
     * @param string $name the property name
     * @param boolean $checkVars whether to treat member variables as properties
     * @return boolean whether the property can be read
     * @see canSetProperty
     */
    public function canGetProperty($name, $checkVars = true);

    /**
     * Returns a value indicating whether a property can be set.
     * A property is writable if:
     *
     * - the class has a setter method associated with the specified name
     *   (in this case, property name is case-insensitive);
     * - the class has a member variable with the specified name (when `$checkVars` is true);
     *
     * @param string $name the property name
     * @param boolean $checkVars whether to treat member variables as properties
     * @return boolean whether the property can be written
     * @see canGetProperty
     */
    public function canSetProperty($name, $checkVars = true);

    /**
     * Returns a value indicating whether a method is defined.
     *
     * The default implementation is a call to php function `method_exists()`.
     * You may override this method when you implemented the php magic method `__call()`.
     *
     * @param string $name the property name
     * @return boolean whether the property is defined
     */
    public function hasMethod($name);

    /**
     * Reset non-static properties.
     * @param bool $autoreset autorest properties.
     */
    public function reset($autoreset = false);
}