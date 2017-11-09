<?php

class Application_View_Promote_Index extends Admin_View_Default {

    public function getJsCodeHtml() {
        $application = $this->getApplication();
        $ios_device = $application->getDevice(1);
        $android_device = $application->getDevice(2);
        $banner_title = $application->getBannerTitle()?$application->getBannerTitle():$application->getName();
        $jscode = '
<script src="'.$this->getExternalUrl("smart-banner/smart-app-banner.js").'"></script>
<script type="text/javascript">
    new SmartBanner({
        daysHidden: 15,
        daysReminder: 90,
        title: "'.$banner_title.'",
        author: "'.$application->getBannerAuthor().'",
        button: "'.$application->getBannerButtonLabel().'"';

        $store = null;
        if($ios_device->getBannerStoreLabel()) {
            $store .= '
            ios: "'.$ios_device->getBannerStoreLabel().'",';
        }

        if($android_device->getBannerStoreLabel()) {
            $store .= '
            android: "'.$android_device->getBannerStoreLabel().'"';
        }

        if($store) {
            if (substr($store, strlen($store) - 1, 1) == ",") {
                $store = substr($store, 0, strlen($store) - 1);
            }
            $store = ',
        store: {'.$store.'
        }';
        }

        $price = null;
        if($ios_device->getBannerStorePrice()) {
            $price .= '
            ios: "'.$ios_device->getBannerStorePrice().'",';
        }

        if($android_device->getBannerStorePrice()) {
            $price .= '
            android: "'.$android_device->getBannerStorePrice().'"';
        }

        if($price) {
            if (substr($price, strlen($price) - 1, 1) == ",") {
                $price = substr($price, 0, strlen($price) - 1);
            }
            $price = '
        price: {'.$price.'
        }';
            if($store) {
                $price = ','.$price;
            }
        }

        $jscode .= $store.$price.'
    });
</script>';

        return htmlentities(trim($jscode));
    }

    public function getMetaHtml() {
        $application = $this->getApplication();
        $ios_device = $application->getDevice(1);
        $android_device = $application->getDevice(2);

        $metacode = '';

        if($ios_device->getStoreAppId() AND $ios_device->getBannerStoreLabel() AND $ios_device->getBannerStorePrice()) {
            $metacode .= '
<meta name="apple-itunes-app" content="app-id='.$ios_device->getStoreAppId().'">';
        }

        if($application->getBundleId() AND $android_device->getBannerStoreLabel() AND $android_device->getBannerStorePrice()) {
            $metacode .='
<meta name="google-play-app" content="app-id='.$application->getPackageName().'">';
        }

        $metacode .= '
<link rel="stylesheet" href="'.$this->getExternalUrl("smart-banner/smart-app-banner.css").'" type="text/css" media="screen">
<link rel="apple-touch-icon" href="'.$this->getApplication()->getIconUrl(64).'">
<link rel="android-touch-icon" href="'.$this->getApplication()->getIconUrl(64).'" />';

        return htmlentities(trim($metacode));
    }

}
