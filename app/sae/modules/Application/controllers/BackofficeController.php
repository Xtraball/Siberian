<?php

class Application_BackofficeController extends Backoffice_Controller_Default
{

    public function listAction() {
        $this->loadPartials();
    }

    public function editAction() {

        if($app_id = $this->getRequest()->getParam('app_id')) {
            $application = new Application_Model_Application();
            $application->find($app_id);
            if(!$application->getId()) {
                $this->getSession()->addError(__('This application does not exist'));
                $this->_redirect('application/backoffice/list');
            } else {
                $this->loadPartials();
                $this->getLayout()->getPartial('content')->setCurrentApplication($application);
            }
        }
    }

    public function savepostAction() {

        if($datas = $this->getRequest()->getPost()) {

            $application = new Application_Model_Application();

            try {

                if(!empty($datas['app_id'])) {
                    $application->find($datas['app_id']);
                    if(!$application->getId()) {
                        throw new Exception(__('An error occurred while saving the application. Please try again later.'));
                    }

                    $application->addData($datas)
                        ->save()
                    ;
                }

                if(empty($datas['bundle_id'])) {
                    throw new Exception(__('The Bundle Id is required'));
                }

                $this->getSession()->addSuccess(__('The application has been successfully saved'));
                $this->_redirect('application/backoffice/list');

            }
            catch(Exception $e) {
                $this->getSession()->addError($e->getMessage());
                if($application->getId()) {
                    $this->_redirect('application/backoffice/edit', array('app_id' =>  $application->getId()));
                } else {
                    $this->_redirect('application/backoffice/new');
                }
            }

        }

    }

    public function deleteAction() {

        if($app_id = $this->getRequest()->getParam('app_id')) {

            try {

                $application = new Application_Model_Application();
                $application->find($app_id);

                if(!$application->getId()) {
                    throw new Exception(__("This application does not exist"));
                }

                $application->delete();

                $html = array(
                    'success' => 1,
                    'app_id' => $app_id
                );

            }
            catch(Exception $e) {
                $html = array(
                    'error' => 1,
                    'message' => $e->getMessage()
                );
            }

            $this->_sendHtml($html);

        }

    }

    public function uploadAction() {

        if (!empty($_FILES)) {

            try {

                $path = '/var/apps/iphone/certificates/';
                $base_path = Core_Model_Directory::getBasePathTo($path);
                $filename = uniqid().'.pem';
                $app_id = $this->getRequest()->getParam('app_id');

                if(!is_dir($base_path)) mkdir($base_path, 0775, true);

                $adapter = new Zend_File_Transfer_Adapter_Http();
                $adapter->setDestination($base_path);

                $adapter->setValidators(array('Extension' => array('pem', 'case' => false)));
                $adapter->getValidator('Extension')->setMessages(array(
                    'fileExtensionFalse' => __("Extension not allowed, \'%s\' only", '%extension%')
                ));

                $files = $adapter->getFileInfo();

                foreach ($files as $file => $info) {

                    if (!$adapter->isUploaded($file)) {
                        throw new Exception(__('An error occurred during process. Please try again later.'));
                    } else if (!$adapter->isValid($file)) {
                        if(count($adapter->getMessages()) == 1) {
                            $erreur_message = __('Error : <br/>');
                        } else {
                            $erreur_message = __('Errors : <br/>');
                        }
                        foreach($adapter->getMessages() as $message) {
                            $erreur_message .= '- '.$message.'<br/>';
                        }
                        throw new Exception($erreur_message);
                    } else {

                        $adapter->addFilter(new Zend_Filter_File_Rename(array(
                            'target' => $base_path . $filename,
                            'overwrite' => true
                        )));

                        $adapter->receive($file);

                    }
                }

                $certificat = new Push_Model_Certificate();
                $certificat->find(array('type' => 'ios', 'app_id' => $app_id));
                if(!$certificat->getId()) {
                    $certificat->setType('ios')
                        ->setAppId($app_id)
                    ;
                }
                $certificat->setPath($path.$filename)->save();

                $datas = array(
                    'success' => 1,
                    'files' => 'eeeee',
                    'message_success' => __('Info successfully saved'),
                    'message_button' => 0,
                    'message_timeout' => 2,
                );
            } catch (Exception $e) {
                $datas = array(
                    'error' => 1,
                    'message' => $e->getMessage()
                );
            }
            $this->getLayout()->setHtml(Zend_Json::encode($datas));
        }

    }

}
