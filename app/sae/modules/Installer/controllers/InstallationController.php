<?php

class Installer_InstallationController extends Installer_Controller_Installation_Default {

    public function indexAction() {
        $this->loadPartials();
    }

    public function savelicenseAction() {

        try {
            if(Installer_Model_Installer::isInstalled() === false) {
                $request = $this->getRequest();
                System_Model_Config::setValueFor("siberiancms_key",$request->getParam("key"));
                $html = array('success' => 1);
            } else {
                throw new Exception("An error occured while saving the license.");
            }

        } catch (Exception $e) {
            $html = array('message' => $e->getMessage());
            $this->getResponse()->setHttpResponseCode(400);
        }
        $this->getLayout()->setHtml(Zend_Json::encode($html));
    }

    public function endAction() {

        try {
            if(Installer_Model_Installer::setIsInstalled()) {
                $protocol = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
                Siberian_Autoupdater::configure($protocol.$this->getRequest()->getHttpHost());

                # Save installation date
                $system_config = new System_Model_Config();
                $system_config
                    ->setCode("installation_date")
                    ->setLabel("Installation date")
                    ->setValue(date("Y-m-d H:i:s"))
                    ->save();

                $html = array('success' => 1);
            } else {
                throw new Exception("An error occured while finalizing the installation.");
            }

        } catch (Exception $e) {
            $html = array('message' => $e->getMessage());
            $this->getResponse()->setHttpResponseCode(400);
        }

        $this->getLayout()->setHtml(Zend_Json::encode($html));

    }

}