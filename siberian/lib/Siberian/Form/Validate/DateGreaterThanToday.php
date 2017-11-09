<?php

class Siberian_Form_Validate_DateGreaterThanToday extends Zend_Validate_Abstract {

    const DATE_INVALID = 'dateInvalid';

    protected $_messageTemplates = array(
        self::DATE_INVALID => "'%value%' must be greater than today"
    );

    public function isValid($value) {
        $this->_setValue($value);

        $today = new Zend_Date();
        $against = new Zend_Date($value, 'y-MM-dd');

        // expecting $value to be YYYY-MM-DD!
        if ($against < $today) {
            $this->_error(self::DATE_INVALID);
            return false;
        }

        return true;
    }
}