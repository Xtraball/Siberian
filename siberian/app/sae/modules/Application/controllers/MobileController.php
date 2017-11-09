<?php

class Application_MobileController extends Application_Controller_Mobile_Default {

    public function defaultAction() {
        $this->loadPartials('front_index_index');
        $this->getLayout()->setHtml($this->getLayout()->toJson());
    }

    public function languagesAction() {
        $this->getLayout()->setHtml(implode(",", Core_Model_Language::getLanguageCodes()));
    }

    /**
     * @deprecated Siberian 5.0, moved to front/mobile/loadv2
     * regrouping startup items in once.
     */
    public function generatewebappconfigAction() {

        //Generate manifest
        $generic_manifest = '
            {
              "name": "%APP_NAME",
              "icons": [
                {
                  "src": "%ICON",
                  "sizes": "192x192",
                  "type": "image/png",
                  "density": 4.0
                }
              ],
              "display": "standalone"
            }
        ';

        $generic_manifest = str_replace("%APP_NAME", $this->getApplication()->getShortName(), $generic_manifest);
        $generic_manifest = str_replace("%ICON", $this->getApplication()->getIcon(), $generic_manifest);

        $manifest_name_base = Core_Model_Directory::getTmpDirectory(true)."/webapp_manifest_".$this->getApplication()->getId().".json";
        $manifest_name = Core_Model_Directory::getTmpDirectory()."/webapp_manifest_".$this->getApplication()->getId().".json";

        $fp = fopen($manifest_name_base, 'w');
        fwrite($fp, $generic_manifest);
        fclose($fp);

        //Collect images and manifest url
        $data = array(
            "startup_image_url" => $this->getApplication()->getStartupImageUrl(),
            "icon_url" => $this->getApplication()->getIcon(),
            "manifest_url" => $manifest_name
        );


        $this->_sendHtml($data);
    }

}
