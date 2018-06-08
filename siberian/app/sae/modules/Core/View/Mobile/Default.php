<?php

/**
 * Class Core_View_Mobile_Default
 */
class Core_View_Mobile_Default extends Core_View_Default
{
    /**
     * @var
     */
    protected static $_current_option;

    /**
     * @var int
     */
    protected $_design_type_id = 1;

    /**
     * @return bool
     */
    public function isOverview()
    {
        return (bool)$this->getSession()->isOverview;
    }

    /**
     * @param $option
     */
    public static function setCurrentOption($option)
    {
        self::$_current_option = $option;
    }

    /**
     * @return mixed
     */
    public function getCurrentOption()
    {
        return self::$_current_option;
    }

}