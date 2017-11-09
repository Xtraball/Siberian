<?php

class Mcommerce_ApplicationController extends Application_Controller_Default_Ajax
{

    public function loadtabAction() {

        try {

            if($tab_id = $this->getRequest()->getParam('tab_id')) {

                $html = $this->getLayout()->addPartial('tab_html', 'admin_view_default', 'mcommerce/application/edit/'.$tab_id.'.phtml')
                    ->setOptionValue($this->getCurrentOptionValue())
                    ->toHtml()
                ;
                $html = array('tab_html' => $html);

            }
            else {
                throw new Exception($this->_('An error occurred during the process. Please try again later.'));
            }

        }
        catch(Exception $e) {
            $html = array(
                'error' => 1,
                'message' => $e->getMessage(),
                'message_button' => 1,
                'message_loader' => 1
            );
        }

        $this->_sendHtml($html);

    }

    public function saveAction() {

        try {
            if($datas = $this->getRequest()->getPost()) {

                if(empty($datas['name']) OR empty($datas['description'])) throw new Exception($this->_('Please, fill out all fields'));

                $mcommerce = $this->getCurrentOptionValue()->getObject();
                if(!$mcommerce->getId()) {
                    $mcommerce->setValueId($this->getCurrentOptionValue()->getId());
                }

                $mcommerce->setName($datas['name'])
                    ->setDescription($datas['description'])
                    ->save()
                ;

                $html = array(
                    'success' => '1',
                    'create_store' => $mcommerce->getStores()->count() == 0,
                    'success_message' => $this->_('Info successfully saved'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                );

            }
            else {
                throw new Exception($this->_('An error occurred while saving. Please try again later.'));
            }

        }
        catch(Exception $e) {
            $html = array(
                'error' => 1,
                'message' => $e->getMessage(),
                'message_button' => 1,
                'message_loader' => 1
            );
        }

        $this->_sendHtml($html);

    }

}