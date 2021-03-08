<?php

use Siberian\AdNetwork;

/**
 * Class Application_Form_Admob
 */
class Application_Form_Admob extends Siberian_Form_Abstract
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
            ->setAction(__path('application/customization_publication_infos/save-admob'))
            ->setAttrib('id', 'form-general-information');

        self::addClass('create', $this);

        $htmlAdmobAppId = '
<div class="col-md-12"><div class="alert alert-warning">' . p__('application', 'Latest store updates require you to register an AdMob app, and set the ID below.') . '</div></div>';
        $this->addSimpleHtml(
            'html_admob_app_id',
            $htmlAdmobAppId);

        $useAds = $this->addSimpleCheckbox('use_ads', __('Monetize my app using AdMob'));

        if (AdNetwork::$mediationEnabled && canAccess('editor_publication_admob_mediation')) {
            $this->addSimpleCheckbox('mediation_facebook', __('Enable Facebook audience network mediation'));
            $this->addSimpleCheckbox('mediation_startapp', __('Enable StartApp network mediation'));
        }

        $testAds = $this->addSimpleCheckbox('test_ads', p__('application', 'Display only test ads (useful when developing the application)'));



        $htmlAdmob2 = '
<div class="col-md-12">
    <a href="https://www.google.com/admob/" target="_blank" style="text-decoration: underline; cursor: pointer;">
        ' . __('Click here for more information about AdMob') . '
    </a>
</div>';
        $this->addSimpleHtml(
            'html_admob_2',
            $htmlAdmob2
            );

        $htmlAdmob1 = '
<div class="col-md-12">' . __('Enter your AdMob ID for each platform.') . '</div>';
        $this->addSimpleHtml(
            'html_admob_1',
            $htmlAdmob1);

        $adTypes = [
            'banner' => __('Banner'),
            'interstitial' => __('Interstitial'),
            'banner-interstitial' => __('Banner & Interstitial'),
        ];

        // iOS Ads
        $iosAdmobAppId = $this->addSimpleText(
            'ios_admob_app_id',
            __('App ID'),
            'example: ca-app-id-3940256099942544~6300978111',
            true);
        $iosAdmobAppId->setRequired(true);
        $iosAdmobId = $this->addSimpleText(
            'ios_admob_id',
            __('Banner ID'),
            'example: ca-app-pub-3940256099942544/6300978111',
            true);
        $iosAdmobInterstitialId = $this->addSimpleText(
            'ios_admob_interstitial_id',
            __('Interstitial ID'),
            'example: ca-app-pub-3940256099942544/1033173712',
            true);
        $iosAdmobType = $this->addSimpleSelect(
            'ios_admob_type',
            __('Ads type'),
            $adTypes);

        $this->groupElements(
            'ios',
            [
                'ios_admob_app_id',
                'ios_admob_id',
                'ios_admob_interstitial_id',
                'ios_admob_type',
            ],
            __('iOS'));

        // Android ads
        $androidAdmobAppId = $this->addSimpleText(
            'android_admob_app_id',
            __('App ID'),
            'example: ca-app-id-3940256099942544~6300978111',
            true);
        $androidAdmobAppId->setRequired(true);
        $androidAdmobId = $this->addSimpleText(
            'android_admob_id',
            __('Banner ID'),
            'example: ca-app-pub-3940256099942544/6300978111',
            true);
        $androidAdmobInterstitialId = $this->addSimpleText(
            'android_admob_interstitial_id',
            __('Interstitial ID'),
            'example: ca-app-pub-3940256099942544/1033173712',
            true);
        $androidAdmobType = $this->addSimpleSelect(
            'android_admob_type',
            __('Ads type'),
            $adTypes);

        $this->groupElements(
            'android',
            [
                'android_admob_app_id',
                'android_admob_id',
                'android_admob_interstitial_id',
                'android_admob_type',
            ],
            __('Android'));

        $submit = $this->addSubmit(__('Save'));
        $submit->addClass('pull-right');
    }

    /**
     * @param $application
     * @return $this
     */
    public function fill($application)
    {
        $this->getElement('use_ads')->setValue((boolean) $application->getUseAds());
        $this->getElement('test_ads')->setValue((boolean) $application->getTestAds());

        $androidDevice = $application->getAndroidDevice();

        $this->getElement('android_admob_app_id')->setValue($androidDevice->getAdmobAppId());
        $this->getElement('android_admob_id')->setValue($androidDevice->getAdmobId());
        $this->getElement('android_admob_interstitial_id')->setValue($androidDevice->getAdmobInterstitialId());
        $this->getElement('android_admob_type')->setValue($androidDevice->getAdmobType());

        $iosDevice = $application->getIosDevice();

        $this->getElement('ios_admob_app_id')->setValue($iosDevice->getAdmobAppId());
        $this->getElement('ios_admob_id')->setValue($iosDevice->getAdmobId());
        $this->getElement('ios_admob_interstitial_id')->setValue($iosDevice->getAdmobInterstitialId());
        $this->getElement('ios_admob_type')->setValue($iosDevice->getAdmobType());

        if (AdNetwork::$mediationEnabled && canAccess('editor_publication_admob_mediation')) {
            $this->getElement('mediation_facebook')->setValue((boolean) $application->getMediationFacebook());
            $this->getElement('mediation_startapp')->setValue((boolean) $application->getMediationStartapp());
        }

        return $this;
    }
}
