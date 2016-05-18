<?php

class Translation_JsController extends Core_Controller_Default
{

    public function validateAction() {
        
        $data_translations = array(
            "required" => $this->_("This field is required."),
            "remote" => $this->_("Please fix this field."),
            "email" => $this->_("Please enter a valid email address."),
            "url" => $this->_("Please enter a valid URL."),
            "date" => $this->_("Please enter a valid date."),
            "dateISO" => $this->_("Please enter a valid date (ISO)."),
            "number" => $this->_("Please enter a valid number."),
            "digits" => $this->_("Please enter only digits."),
            "creditcard" => $this->_("Please enter a valid credit card number."),
            "equalTo" => $this->_("Please enter the same value again."),
            "maxlength" => $this->_("Please enter no more than {0} characters."),
            "minlength" => $this->_("Please enter at least {0} characters."),
            "rangelength" => $this->_("Please enter a value between {0} and {1} characters long."),
            "range" => $this->_("Please enter a value between {0} and {1}."),
            "max" => $this->_("Please enter a value less than or equal to {0}."),
            "min" => $this->_("Please enter a value greater than or equal to {0}.")
        );
        
        $datas = array(
            "languagecode" => current(explode("_", Core_Model_Language::getCurrentLocale())),
            "translations" => $data_translations
        );

        die( json_encode($datas) );
    }
}
