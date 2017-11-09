<?php

class Installer_Installation_ApplicationController extends Installer_Controller_Installation_Default {

    public function createAction() {

        if($datas = $this->getRequest()->getPost()) {

            $application = new Application_Model_Application();
            try {

                if(empty($datas['name'])) {
                    throw new Exception($this->_('Please, enter a name'));
                }

                $privacy_policy = System_Model_Config::getValueFor("privacy_policy");

                $application
                    ->setName($datas['name'])
                    ->setPrivacyPolicy($privacy_policy)
                    ->setAndroidPushIcon("/placeholder/android/push_default_icon.png")
                    ->setAndroidPushColor("#0099C7")
                    ->save()
                ;

                $html = array('success' => 1);

            } catch (Exception $e) {
                $html = array('message' => $e->getMessage());
                $this->getResponse()->setHttpResponseCode(400);
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }

    }

}