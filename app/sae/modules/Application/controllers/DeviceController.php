<?php

class Application_DeviceController extends Core_Controller_Default {

    public function checkAction() {

        if($app_id = $this->getRequest()->getParam('app_id')) {
            $mobile = new Mobile_Detect();
            $url = null;

            if($app_id == "previewer") {

                $previewer = Previewer_Model_Previewer::getInstance();

                if ($mobile->isIosdevice()) {
                    if($previewer->getAppleStoreUrl()) {
                        $url = $previewer->getAppleStoreUrl();
                    }
                } else if ($mobile->isAndroiddevice()) {
                    if($previewer->getPlayStoreUrl()) {
                        $url = $previewer->getPlayStoreUrl();
                    }
                }

                $language_code = Core_Model_Language::DEFAULT_LANGUAGE;
                if($this->getRequest()->getParam('language_code')) {
                    $language_code = $this->getRequest()->getParam('language_code');
                }

                if(!$url) {
                    $url = $this->getUrl("errors/$language_code-no-store.html");
                }

            } else {

                $device_type_id = 0;

                $application = new Application_Model_Application();
                $application->find($app_id);

                $url = $application->getUrl();

                if ($mobile->isIosdevice()) {
                    $device_type_id = 1;
                } else if ($mobile->isAndroiddevice()) {
                    $device_type_id = 2;
                }

                if($device_type_id > 0) {
                    $store_url = $application->getDevice($device_type_id)->getStoreUrl();
                    if($store_url) $url = $store_url;
                }

            }

            if ($url) {
                $this->getResponse()->setRedirect($url)->sendResponse();
                die;
            }
        }

    }

    /**
     * @deprecated 4.8.7+
     */
    public function apkisgeneratedAction() {

        $appName = $this->getRequest()->getParam('app_name');
        $apk_base_path = Core_Model_Directory::getBasePathTo("var/tmp/applications/ionic/android/{$appName}/build/outputs/apk/{$appName}-release.apk");
        $apk_path = Core_Model_Directory::getPathTo("var/tmp/applications/ionic/android/{$appName}/build/outputs/apk/{$appName}-release.apk");

        $apk_is_generated = false;
        $link = $this->getUrl().$apk_path;
        $link = str_replace("//", "/", $link);

        if(file_exists($apk_base_path)) {
            if(time()-filemtime($apk_base_path) <= 600) {
                $apk_is_generated = true;
            }
        }

        $user = new Backoffice_Model_User();

        try {

            $user = $user->findAll(null, "user_id ASC", array("limit" => "1"))->current();

            $layout = $this->getLayout()->loadEmail('application', 'download_source');
            $subject = __('Android APK Generation');
            $layout->getPartial('content_email')->setLink($link);
            $layout->getPartial('content_email')->setApkStatus($apk_is_generated);

            $content = $layout->render();

            # Probably deprecated
            # @version 4.8.7 - SMTP
            $mail = new Siberian_Mail();
            $mail->setBodyHtml($content);
            $mail->addTo($user->getEmail());
            $mail->setSubject($subject);
            $mail->send();

        } catch(Exception $e) {
            $logger = Zend_Registry::get("logger");
            $logger->sendException("Fatal Error Sending the APK Generation Email: \n".print_r($e, true), "apk_generation_");
        }

        die('ok');
    }

    public function downloadappAction() {

        if($app_id = $this->getRequest()->getParam('app_id')) {

            $mobile = new Mobile_Detect();
            $application = new Application_Model_Application();
            $application->find($app_id);
            $redirect_to = $application->getUrl();

            if($mobile->isAndroiddevice()) {
                $store_url = $application->getDevice(2)->getStoreUrl();
                if($store_url) $redirect_to = preg_match('/details/', $store_url)?preg_replace('/(.*\/)((details).*)/', 'market://$2', $store_url):$store_url;
            } else if($mobile->isIosdevice()) {
                $store_url = $application->getDevice(1)->getStoreUrl();
                if($store_url) $redirect_to = str_replace(array('http:', 'https:'), 'itms-apps:', $store_url);
            }

            $this->getResponse()->setRedirect($redirect_to)->sendResponse();
            die;
        }

    }

}
