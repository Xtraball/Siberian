<?php

class Booking_ApplicationController extends Application_Controller_Default
{

    public function editpostAction() {

        if($datas = $this->getRequest()->getPost()) {

            try {
                $application = $this->getApplication();
                $html = '';
                $isNew = true;

                // Test s'il y a une erreur dans la saisie
                if(empty($datas['store_name'])) throw new Exception($this->_('Please, choose a store'));
                if(empty($datas['email'])) throw new Exception($this->_('Please enter a valid email address'));

                // Test s'il y a un value_id
                if(empty($datas['value_id'])) throw new Exception($this->_('An error occurred during process. Please try again later.'));

                // Récupère l'option_value en cours
                $option_value = new Application_Model_Option_Value();
                $option_value->find($datas['value_id']);

                $booking = new Booking_Model_Booking();
                $store = new Booking_Model_Store();
                $booking->find($datas['value_id'], 'value_id');
                // Si un id est passé en paramètre
                if(!empty($datas['store_id'])) {
                    $store->find($datas['store_id']);
                    if($store->getId() AND $booking->getValueId() != $option_value->getId()) {
                        // Envoi l'erreur
                        throw new Exception($this->_('An error occurred during process. Please try again later.'));
                    }
                    $isNew = !$store->getId();
                }

                $booking->setData($datas)->save();
                unset($datas['value_id']);
                $datas['booking_id'] = $booking->getId();
                $store->setData($datas)->save();

                $html = array(
                    'success' => '1',
                    'success_message' => $this->_('Info successfully saved'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                );

                if($isNew) {
                    $html['row_html'] = $this->getLayout()->addPartial('row_'.$store->getId(), 'admin_view_default', 'booking/application/edit/row.phtml')
                        ->setCurrentStore($store)
                        ->setCurrentOptionValue($option_value)
                        ->toHtml()
                    ;
                }


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

    public function deleteAction() {
        try {

            $id = $this->getRequest()->getParam('id');
            $store = new Booking_Model_Store();
            $store->find($id)->delete();

            // Renvoie OK
            $html = array('success' => 1);

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