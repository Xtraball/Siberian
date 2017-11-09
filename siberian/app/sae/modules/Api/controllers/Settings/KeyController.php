<?php

class Api_Settings_KeyController extends Backoffice_Controller_Default
{

    public function listAction() {
        $this->loadPartials();
    }

    public function saveAction() {

        if($datas = $this->getRequest()->getPost()) {

            try {

                if(empty($datas['key_id'])) {
                    throw new Exception($this->_('An error occurred while saving. Please try again later.'));
                }
                $key = new Api_Model_Key();
                $key->find($datas['key_id']);
                if(!$key->getId()) {
                    throw new Exception($this->_('An error occurred while saving. Please try again later.'));
                }

                if(empty($datas['value'])) $datas['value'] = null;

                $key->setValue($datas['value'])->save();

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