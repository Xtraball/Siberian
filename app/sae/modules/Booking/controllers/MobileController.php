<?php

class Booking_MobileController extends Application_Controller_Mobile_Default
{

    public function postAction() {

        if($datas = $this->getRequest()->getPost()) {
            try {

                // Test les eventuelles erreurs
                $errors = array();
                if(empty($datas['name'])) $errors[] = $this->_('Your name');
                if((empty($datas['email']) OR !Zend_Validate::is($datas['email'], 'emailAddress')) && (empty($datas['phone']))) $errors[] = $this->_('Your phone number or email address');
                if(empty($datas['people'])) $errors[] = $this->_('The number of people');
                if(empty($datas['date'])) $errors[] = $this->_('The date and time of the booking');
                if(empty($datas['prestation'])) $errors[] = $this->_('The booking informations');

                $store = new Booking_Model_Store();
                $store->find($datas['store'], 'store_id');
                if(!$store->getId()) throw new Exception($this->_('An error occurred during process. Please try again later.'));

                //vérif value
                $booking = new Booking_Model_Booking();
                $booking->find($store->getBookingId(), 'booking_id');
                if(!$booking->getId() || ($booking->getValueId() != $datas['option_value_id'])) throw new Exception($this->_('An error occurred during process. Please try again later.'));
                $dest_email = $store->getEmail();

                $app_name = $this->getApplication()->getName();

                $layout = $this->getLayout()->loadEmail('booking', 'send_email');
                $layout->getPartial('content_email')->setData($datas);
                $content = $layout->render();

                $mail = new Zend_Mail('UTF-8');
                $mail->setBodyHtml($content);
                $mail->setFrom($datas['email'], $datas['name']);
                $mail->addTo($dest_email, $app_name);
                $mail->setSubject($this->_("Message from your app %s", $app_name));
                $mail->send();

                if(!empty($errors)) {
                    $message = $this->_('Please fill out the following fields:<br />');
                    $message .= join('<br />', $errors);
                    $html = array('error' => 1, 'message' => $message);
                }
                else {
                    $html = array('success' => 1);
                }

            }
            catch(Exception $e) {
                $html = array('error' => 1, 'message' => $e->getMessage());
            }

            $this->_sendHtml($html);
        }
    }

}