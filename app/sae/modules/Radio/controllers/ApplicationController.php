<?php

class Radio_ApplicationController extends Application_Controller_Default
{

    public function editpostAction() {

        if($data = $this->getRequest()->getPost()) {

            try {

                // Test s'il y a un value_id
                if(empty($data['value_id'])) throw new Exception($this->_('An error occurred while saving. Please try again later.'));

                // Récupère l'option_value en cours
                $option_value = new Application_Model_Option_Value();
                $option_value->find($data['value_id']);

                // Test s'il y a embrouille entre la value_id en cours de modification et l'application en session
                if(!$option_value->getId() OR $option_value->getAppId() != $this->getApplication()->getId()) {
                    throw new Exception($this->_('An error occurred while saving. Please try again later.'));
                }

                // Test s'il y a embrouille entre la value_id en cours de modification et l'application en session
                if(empty($data['link']) OR !Zend_Uri::check($data['link'])) {
                    throw new Exception($this->_('Please enter a valid url'));
                }

                // Test if url is not literal IPv4
                $warning_message = Siberian_Network::testipv4($data['link']);

                $radio = new Radio_Model_Radio();
                $radio->find($option_value->getId(), 'value_id');
                if(!$radio->getId()) {
                    $radio->setValueId($data['value_id']);
                }

                $radio->addData($data)
                    ->save()
                ;

                $html = array(
                    'success' => '1',
                    'success_message' => __('Info successfully saved'),
                    'warning_message' => $warning_message,
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                );
                if(!$radio->getIsDeleted()) {
                    $html['link'] = $radio->getLink();
                }

            }
            catch(Exception $e) {
                $html = array(
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            }

            $this->getResponse()
                ->setBody(Zend_Json::encode($html))
                ->sendResponse()
            ;
            die;

        }

    }

}