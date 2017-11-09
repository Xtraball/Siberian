<?php

class Core_View_Mobile_Default extends Core_View_Default
{

    protected static $_current_option;
    protected $_design_type_id = 1;

    public function isOverview() {
        return (bool) $this->getSession()->isOverview;
    }

    public static function setCurrentOption($option) {
        self::$_current_option = $option;
    }

    public function getCurrentOption() {
        return self::$_current_option;
    }

}