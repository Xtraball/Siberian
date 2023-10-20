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

        $enableRegistration = $this->addSimpleCheckbox('enable_registration', p__('customer', 'Self registration'));
        $enablePasswordVerification = $this->addSimpleCheckbox('enable_password_verification', p__('customer', 'Password verification (input twice)'));

        $group1 = $this->groupElements(
            'group1', [
                'design', 'enable_registration', 'enable_password_verification'],
                p__('customer', 'Options')
            );

        $emailValidation = $this->addSimpleCheckbox('email_validation', p__('customer', 'Enable e-mail confirmation'));

        $mobileNumber = $this->addSimpleCheckbox('extra_mobile', p__('customer', 'Mobile number'));
        $mobileNumberRequired = $this->addSimpleCheckbox('extra_mobile_required', p__('customer', 'Mobile number required?'));

        $civility = $this->addSimpleCheckbox('extra_civility', p__('customer', 'Civility'));
        $civilityRequired = $this->addSimpleCheckbox('extra_civility_required', p__('customer', 'Civility required?'));

        $birthdate = $this->addSimpleCheckbox('extra_birthdate', p__('customer', 'Birthdate'));
        $birthdateRequired = $this->addSimpleCheckbox('extra_birthdate_required', p__('customer', 'Birthdate required?'));

        $nickname = $this->addSimpleCheckbox('extra_nickname', p__('customer', 'Nickname'));
        $nicknameRequired = $this->addSimpleCheckbox('extra_nickname_required', p__('customer', 'Nickname required?'));

        $enableCommercialAgreement = $this->addSimpleCheckbox('enable_commercial_agreement', p__('customer', 'Commercial agreement'));
        $commercialAgreementLabel = $this->addSimpleText('enable_commercial_agreement_label', '&nbsp;&gt;&nbsp;' . p__('customer', 'Custom label'));
        $commercialAgreementLabel->setDescription(p__('customer', 'Default') . ': ' . p__('customer', "I'd like to hear about offers & services"));

        $text = p__('customer', 'Please note that some modules can enforce the use of custom fields, and will override the settings below!');
        $fieldsHelper = <<<HELP
<div class="col-md-7 col-md-offset-3">
    <div class="alert alert-warning">{$text}</div>
</div>
HELP;

        $htmlHelper = $this->addSimpleHtml('fields_helper', $fieldsHelper);

        $group2 = $this->groupElements(
            'group2',
            [
                'fields_helper',
                'email_validation',
                'extra_mobile',
                'extra_mobile_required',
                'extra_civility',
                'extra_civility_required',
                'extra_birthdate',
                'extra_birthdate_required',
                'extra_nickname',
                'extra_nickname_required',
                'enable_commercial_agreement',
                'enable_commercial_agreement_label'
            ],
            p__('customer', 'Extra fields')
        );

        $valueId = $this->addSimpleHidden('value_id');

        $save = $this->addSubmit(__('Save'), __('Save'));
        $save->addClass('pull-right');
    }

    /**
     * @param Application_Model_Application $application
     * @throws Zend_Exception
     * @throws \Siberian\Exception
     */
    public function setOverrides (Application_Model_Application $application)
    {
        $features = $application->getOptions();

        $useNickname = [];
        //$useRanking = [];
        $useBirthdate = [];
        $useCivility = [];
        $useMobile = [];

        foreach ($features as $feature) {
            if ($feature->getUseNickname()) {
                $useNickname[] = $feature->getTabbarName() . ' (' . $feature->getCode() . ')';
            }
            //if ($feature->getUseRanking()) {
            //    $useRanking[] = $feature->getTabbarName() . ' (' . $feature->getCode() . ')';
            //}
            if ($feature->getUseBirthdate()) {
                $useBirthdate[] = $feature->getTabbarName() . ' (' . $feature->getCode() . ')';
            }
            if ($feature->getUseCivility()) {
                $useCivility[] = $feature->getTabbarName() . ' (' . $feature->getCode() . ')';
            }
            if ($feature->getUseMobile()) {
                $useMobile[] = $feature->getTabbarName() . ' (' . $feature->getCode() . ')';
            }
        }

        if (!empty($useNickname)) {
            $extraNickname = $this->getElement('extra_nickname');
            $extraNickname->setDescription(implode_polyfill(', ', $useNickname));
            $extraNickname->setValue(true);
            $extraNickname->setAttrib('disabled', 'disabled');

            $extraNicknameRequired = $this->getElement('extra_nickname_required');
            $extraNicknameRequired->setDescription(implode_polyfill(', ', $useNickname));
            $extraNicknameRequired->setValue(true);
            $extraNicknameRequired->setAttrib('disabled', 'disabled');
        }
        //if (!empty($useRanking)) {
        //    $this->getElement('extra_ranking')->setDescription(implode_polyfill(', ', $useRanking));
        //}
        if (!empty($useBirthdate)) {
            $extraBirthdate = $this->getElement('extra_birthdate');
            $extraBirthdate->setDescription(implode_polyfill(', ', $useBirthdate));
            $extraBirthdate->setValue(true);
            $extraBirthdate->setAttrib('disabled', 'disabled');

            $extraBirthdateRequired = $this->getElement('extra_birthdate_required');
            $extraBirthdateRequired->setDescription(implode_polyfill(', ', $useBirthdate));
            $extraBirthdateRequired->setValue(true);
            $extraBirthdateRequired->setAttrib('disabled', 'disabled');
        }
        if (!empty($useCivility)) {
            $extraCivility = $this->getElement('extra_civility');
            $extraCivility->setDescription(implode_polyfill(', ', $useCivility));
            $extraCivility->setValue(true);
            $extraCivility->setAttrib('disabled', 'disabled');

            $extraCivilityRequired = $this->getElement('extra_civility_required');
            $extraCivilityRequired->setDescription(implode_polyfill(', ', $useCivility));
            $extraCivilityRequired->setValue(true);
            $extraCivilityRequired->setAttrib('disabled', 'disabled');
        }
        if (!empty($useMobile)) {
            $extraMobile = $this->getElement('extra_mobile');
            $extraMobile->setDescription(implode_polyfill(', ', $useMobile));
            $extraMobile->setValue(true);
            $extraMobile->setAttrib('disabled', 'disabled');

            $extraMobileRequired = $this->getElement('extra_mobile_required');
            $extraMobileRequired->setDescription(implode_polyfill(', ', $useMobile));
            $extraMobileRequired->setValue(true);
            $extraMobileRequired->setAttrib('disabled', 'disabled');
        }
    }
}
