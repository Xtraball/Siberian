<?php

class Application_View_Promote_Index extends Admin_View_Default {

    public function getJsCodeHtml() {
        $application = $this->getApplication();
        $ios_device = $application->getDevice(1);
        $android_device = $application->getDevice(2);
        $banner_title = $application->getBannerTitle()?$application->getBannerTitle():$application->getName();

        $jscode = Application_Model_Promote_Banner::script($this->getExternalUrl("smart-banner/smart-app-banner.js"), [
            "title" => $banner_title,
            "author" => $application->getBannerAuthor(),
            "button" => $application->getBannerButtonLabel(),
            "store" => [
                "ios" => $ios_device->getBannerStoreLabel(),
                "android" => $android_device->getBannerStoreLabel(),
            ],
            "price" => [
                "ios" => $ios_device->getBannerStorePrice(),
                "android" => $android_device->getBannerStorePrice(),
            ],
        ]);

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
