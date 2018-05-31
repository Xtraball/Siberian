<?php

/**
 * Class Siberian_Form_Validate_DateGreaterThanToday
 */
class Siberian_Form_Validate_DateGreaterThanToday extends Zend_Validate_Abstract
{
    /**
     * @var string
     */
    const DATE_INVALID = 'dateInvalid';

    /**
     * @var array
     */
    protected $_messageTemplates = [
        self::DATE_INVALID => "'%value%' must be greater than today"
    ];

    /**
     * @param mixed $value
     * @return bool
     * @throws Zend_Date_Exception
     */
    public function isValid($value)
    {
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