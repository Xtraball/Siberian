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

        $enableFacebookLogin = $this->addSimpleCheckbox('enable_facebook_login', p__('customer', 'Facebook login'));
        $enableRegistration = $this->addSimpleCheckbox('enable_registration', p__('customer', 'Self registration'));

        $enableCommercialAgreement = $this->addSimpleCheckbox('enable_commercial_agreement', p__('customer', 'Commercial agreement'));
        $commercialAgreementLabel = $this->addSimpleText('enable_commercial_agreement_label', '&nbsp;&gt;&nbsp;' . p__('customer', 'Custom label'));
        $commercialAgreementLabel->setDescription(p__('customer', 'Default') . ': ' . p__('customer', "I'd like to hear about offers & services"));

        $valueId = $this->addSimpleHidden('value_id');

        $save = $this->addSubmit(__('Save'), __('Save'));
        $save->addClass('pull-right');
    }
}