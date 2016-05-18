<?php

class Installer_Installation_ApplicationController extends Installer_Controller_Installation_Default {

    public function createAction() {

        if($datas = $this->getRequest()->getPost()) {

            $application = new Application_Model_Application();
            try {

                if(empty($datas['name'])) {
                    throw new Exception($this->_('Please, enter a name'));
                }

                $application->setName($datas['name'])
                    ->save()
                ;

//                rename(APPLICATION_PATH.'/Bootstrap.php', APPLICATION_PATH.'/Bootstrap.old.php');
//                rename(APPLICATION_PATH.'/Bootstrap.new.php', APPLICATION_PATH.'/Bootstrap.php');

                $html = array('success' => 1);

            } catch (Exception $e) {
                $html = array('message' => $e->getMessage());
                $this->getResponse()->setHttpResponseCode(400);
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }

    }

}