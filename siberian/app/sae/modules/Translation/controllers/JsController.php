<?php

class Translation_JsController extends Core_Controller_Default {

    public function validateAction() {
        
        $data_translations = array(
            "required" => __("This field is required."),
            "remote" => __("Please fix this field."),
            "email" => __("Please enter a valid email address."),
            "url" => __("Please enter a valid URL."),
            "date" => __("Please enter a valid date."),
            "dateISO" => __("Please enter a valid date (ISO)."),
            "number" => __("Please enter a valid number."),
            "digits" => __("Please enter only digits."),
            "creditcard" => __("Please enter a valid credit card number."),
            "equalTo" => __("Please enter the same value again."),
            "maxlength" => __("Please enter no more than {0} characters."),
            "minlength" => __("Please enter at least {0} characters."),
            "rangelength" => __("Please enter a value between {0} and {1} characters long."),
            "range" => __("Please enter a value between {0} and {1}."),
            "max" => __("Please enter a value less than or equal to {0}."),
            "min" => __("Please enter a value greater than or equal to {0}.")
        );
        
        $datas = array(
            "languagecode" => current(explode("_", Core_Model_Language::getCurrentLocale())),
            "translations" => $data_translations
        );

        $this->_sendJson($datas);
    }
}
