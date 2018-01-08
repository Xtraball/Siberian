<?php

class Application_SecurityController extends Core_Controller_Default_Abstract {

    public function checkAction() {

        /*
         * If:     1) It's a request to the editor
         *         2) The request is an ajax request
         *         3) The app_id param is sent in the request
         *         4) The app_id param is different of the id of the application currently in the session, i.e. an old tab
         * then:   Throw an exception signaling the editor should be refreshed
         */

        /* !!! Note: In this controller is_editor is always true !!! */
        $is_editor = true;
        $app_id = $this->getRequest()->getParam('app_id');
        $is_ajax = $this->getRequest()->isXmlHttpRequest();
        $old_tab = $app_id != $this->getSession()->getApplication()->getAppId();

        if (false /**$is_editor && $is_ajax && $app_id && $old_tab*/) {
            $this->_sendJson(array(
                "error" => true,
                "refresh" => true
            ));
        } else {
            $this->_sendJson(array(
                "succes" => true,
                "refresh" => false
            ));
        }
    }

    public function plistAction () {
        $plist = new PListEditor\PListEditor();
        $plist->readFile('/Volumes/SSD2/Developments/repos/xtraball.com/siberian-2/ionic/platforms/ios/AppsMobileCompany/AppsMobileCompany-Info.plist');
        // Preview PList

        $dom = new DOMDocument('1.0', 'utf-8');

        $root = $plist->root();
        $root->removeProperty('CFBundleDisplayName');
        $root->addProperty(\PListEditor\PListProperty::PL_ARRAY, [
            'toto',
            'tata'
        ], 'CFBundleDisplayName');

        echo 'Preview' . PHP_EOL . '<br />';
        echo '<pre>';
        print_r(htmlspecialchars($plist->preview()));

        die();
    }
}