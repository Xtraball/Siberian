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

}
