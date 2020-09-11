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

        $design = $this->addSimpleSelect('design', p__('customer', 'Design'), [
            'list' => p__('customer', 'List'),
            'card' => p__('customer', 'Card'),
        ]);

        $enableFacebookLogin = $this->addSimpleCheckbox('enable_facebook_login', p__('customer', 'Facebook login'));
        $enableRegistration = $this->addSimpleCheckbox('enable_registration', p__('customer', 'Self registration'));
        $enablePasswordVerification = $this->addSimpleCheckbox('enable_password_verification', p__('customer', 'Password verification (input twice)'));

        $group1 = $this->groupElements(
            'group1', [
                'design', 'enable_facebook_login', 'enable_registration', 'enable_password_verification'],
                p__('customer', 'Options')
            );

        $mobileNumber = $this->addSimpleCheckbox('extra_mobile', p__('customer', 'Mobile number'));
        $mobileNumberRequired = $this->addSimpleCheckbox('extra_mobile_required', p__('customer', 'Mobile number required?'));

        $civility = $this->addSimpleCheckbox('extra_civility', p__('customer', 'Civility'));
        $civilityRequired = $this->addSimpleCheckbox('extra_civility_required', p__('customer', 'Civility required?'));

        $enableCommercialAgreement = $this->addSimpleCheckbox('enable_commercial_agreement', p__('customer', 'Commercial agreement'));
        $commercialAgreementLabel = $this->addSimpleText('enable_commercial_agreement_label', '&nbsp;&gt;&nbsp;' . p__('customer', 'Custom label'));
        $commercialAgreementLabel->setDescription(p__('customer', 'Default') . ': ' . p__('customer', "I'd like to hear about offers & services"));

        $group2 = $this->groupElements(
            'group2', [
            'extra_mobile', 'extra_mobile_required', 'extra_civility', 'extra_civility_required', 'enable_commercial_agreement', 'enable_commercial_agreement_label'],
            p__('customer', 'Extra fields')
        );

        $valueId = $this->addSimpleHidden('value_id');

        $save = $this->addSubmit(__('Save'), __('Save'));
        $save->addClass('pull-right');
    }
}
