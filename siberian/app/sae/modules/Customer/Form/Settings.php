<?php

/**
 * Class Customer_Form_Settings
 */
class Customer_Form_Settings extends Siberian_Form_Abstract
{

    /**
     * @throws Zend_Form_Exception
     */
    public function init()
    {
        parent::init();

        $this
            ->setAction(__path('/customer/application/edit-settings'))
            ->setAttrib('id', 'form-customer-settings');

        // Bind as a create form!
        self::addClass('create', $this);

        $enableFacebookLogin = $this->addSimpleCheckbox('enable_facebook_login', __('Enable Facebook login'));
        $enableRegistration = $this->addSimpleCheckbox('enable_registration', __('Enable registration'));

        $valueId = $this->addSimpleHidden('value_id');

        $save = $this->addSubmit(__("Save"), __("Save"));
        $save->addClass("pull-right");
    }
}