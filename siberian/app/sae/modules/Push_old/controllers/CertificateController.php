<?php

class Push_CertificateController extends Backoffice_Controller_Default {

    public function listAction() {
        $this->loadPartials();
    }

    public function saveAction() {

        if($datas = $this->getRequest()->getPost()) {

            try {

                if(empty($datas['type'])) {
                    throw new Exception('An error occurred during process. Please try again later.');
                }
                if(empty($datas['path'])) $datas['path'] = null;

                $certificat = new Push_Model_Certificate();
                $certificat->find($datas['type'], 'type');
                if(!$certificat->getId()) {
                    $certificat->setType($datas['type']);
                }
                $certificat->setPath($datas['path'])->save();

                $html = array(
                    'success' => '1',
                    'success_message' => $this->_('Info successfully saved'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                );

            }
            catch(Exception $e) {
                $html = array(
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));

        }

    }

}