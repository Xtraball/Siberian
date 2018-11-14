<?php

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

        $useAds = $this->addSimpleCheckbox('use_ads', __('Monetize my app using AdMob'));

        $htmlAdmob1 = '
<div class="col-md-12">' . __('Enter your AdMob ID for each platform.') . '</div>';
        $this->addSimpleHtml(
            'html_admob_1',
            $htmlAdmob1);

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

        $adTypes = [
            'banner' => __('Banner'),
            'interstitial' => __('Interstitial'),
            'banner-interstitial' => __('Banner & Interstitial'),
        ];

        // Android ads
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
                'android_admob_id',
                'android_admob_interstitial_id',
                'android_admob_type',
            ],
            __('Android'));

        // iOS Ads
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
                'ios_admob_id',
                'ios_admob_interstitial_id',
                'ios_admob_type',
            ],
            __('iOS'));

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

        $androidDevice = $application->getAndroidDevice();

        $this->getElement('android_admob_id')->setValue($androidDevice->getAdmobId());
        $this->getElement('android_admob_interstitial_id')->setValue($androidDevice->getAdmobInterstitialId());
        $this->getElement('android_admob_type')->setValue($androidDevice->getAdmobType());

        $iosDevice = $application->getIosDevice();

        $this->getElement('ios_admob_id')->setValue($iosDevice->getAdmobId());
        $this->getElement('ios_admob_interstitial_id')->setValue($iosDevice->getAdmobInterstitialId());
        $this->getElement('ios_admob_type')->setValue($iosDevice->getAdmobType());

        return $this;
    }
}