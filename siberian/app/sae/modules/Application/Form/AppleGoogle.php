<?php

/**
 * Class Application_Form_AppleGoogle
 */
class Application_Form_AppleGoogle extends Siberian_Form_Abstract
{
    /**
     * @var string
     */
    public $color = 'color-green';

    /**
     * @throws Zend_Form_Exception
     */
    public function init()
    {
        parent::init();

        $this
            ->setAction(__path('application/customization_publication_infos/save-apple-google'))
            ->setAttrib('id', 'form-general-information');

        self::addClass('create', $this);

        $applePublicationOptions = [
            '1' => __('I have an Apple developer account'),
            '0' => __('I don\'t have an Apple developer account and I want to create one'),
            //'2' => __('I let you publish my application under your Apple developer account'),
        ];

        // Apple
        $appleChoice = $this->addSimpleRadio(
            'has_apple_account',
            __('Publication type'),
            $applePublicationOptions);

        $appleEmail = $this->addSimpleText('apple_email', __('E-mail'));
        $applePassword = $this->addSimpleText('apple_password', __('Password'));

        $appleHtml = '
<div class="col-md-11 alert alert-info">
    ' . __('You have to create an Apple account before publishing your application.<br /><a href="https://appleid.apple.com/account#!&page=create" target="_blank">Click here to create one.</a>') . '
</div>';
        $warningApple = $this->addSimpleHtml('warning_apple', $appleHtml);

        $this->groupElements(
            'apple',
            [
                'has_apple_account',
                'apple_email',
                'apple_password',
                'warning_apple',
            ],
            __('Apple'));

        // Google
        $googlePublicationOptions = [
            '1' => __('I have a Google developer account'),
            '0' => __('I don\'t have a Google developer account and I want to create one'),
            //'2' => __('I let you publish my application under your Google developer account'),
        ];

        $googleChoice = $this->addSimpleRadio(
            'has_android_account',
            __('Publication type'),
            $googlePublicationOptions);

        $googleEmail = $this->addSimpleText('android_email', __('E-mail'));
        $googlePassword = $this->addSimpleText('android_password', __('Password'));

        $googleHtml = '
<div class="col-md-11 alert alert-info">
    ' . __('You have to create a Google account before publishing your application.<br /><a href="https://play.google.com/apps/publish/signup" target="_blank">Click here to create one.</a>') . '
</div>';
        $warningGoogle = $this->addSimpleHtml('warning_android', $googleHtml);

        $this->groupElements(
            'google',
            [
                'has_android_account',
                'android_email',
                'android_password',
                'warning_android',
            ],
            __('Google'));

        $submit = $this->addSubmit(__('Save'));
        $submit->addClass('pull-right');
    }

    /**
     * @param $application
     * @return $this
     */
    public function fill($application)
    {
        $androidDevice = $application->getAndroidDevice();

        $this->getElement('android_email')->setValue($androidDevice->getDeveloperAccountUsername());
        $this->getElement('android_password')->setValue($androidDevice->getDeveloperAccountPassword());
        $this->getElement('has_android_account')->setValue($androidDevice->getUseOurDeveloperAccount() ? 2 : 1);

        $iosDevice = $application->getIosDevice();

        $this->getElement('apple_email')->setValue($iosDevice->getDeveloperAccountUsername());
        $this->getElement('apple_password')->setValue($iosDevice->getDeveloperAccountPassword());
        $this->getElement('has_apple_account')->setValue($iosDevice->getUseOurDeveloperAccount() ? 2 : 1);

        return $this;
    }
}
