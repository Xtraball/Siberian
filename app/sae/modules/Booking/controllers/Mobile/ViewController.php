<?php

class Booking_Mobile_ViewController extends Application_Controller_Mobile_Default {

    public function findAction() {

        if($value_id = $this->getRequest()->getParam('value_id')) {

            $booking = $this->getCurrentOptionValue()->getObject();
            $data = array("stores" => array());

            if($booking->getId()) {

                $store = new Booking_Model_Store();
                $stores = $store->findAll(array('booking_id' => $booking->getId()));

                foreach($stores as $store) {
                    $data["stores"][] = array(
                        'id' => $store->getId(),
                        'name' => $store->getStoreName()
                    );
                }

            }

            $data['page_title'] = $this->getCurrentOptionValue()->getTabbarName();

            $this->_sendHtml($data);

        }

    }

    public function postAction() {

        if($data = Zend_Json::decode($this->getRequest()->getRawBody())) {

            try {

                $errors = array();

                if(empty($data['name'])) $errors[] = $this->_('Your name');
                if((empty($data['email']) OR !Zend_Validate::is($data['email'], 'emailAddress')) && (empty($data['phone']))) $errors[] = $this->_('Your phone number or email address');
                if(empty($data['store'])) $errors[] = $this->_('Location');
                if(empty($data['people'])) $errors[] = $this->_('The number of people');
                if(empty($data['date'])) $errors[] = $this->_('The date and time of the booking');
                if(empty($data['prestation'])) $errors[] = $this->_('The booking information');
//                $date = new Zend_Date($data['date']);
//                if(!empty($data['date']) AND $date->compare(Zend_Date::now(), Zend_Date::DATES) < 0) throw new Exception($this->_('Please, enter a date greater than today'));

                if(!empty($errors)) {
                    $message = $this->_('Please fill out the following fields: ');
                    $message .= join(' - ', $errors);
                    $html = array('error' => 1, 'message' => $message);
                }
                else {
                    $store = new Booking_Model_Store();
                    $store->find($data['store'], 'store_id');
                    if(!$store->getId()) throw new Exception($this->_('An error occurred during process. Please try again later.'));
                    $data["location"] = $store->getStoreName();

                    //vérif value
                    $booking = new Booking_Model_Booking();
                    $booking->find($store->getBookingId(), 'booking_id');
                    if(!$booking->getId()) throw new Exception($this->_('An error occurred during process. Please try again later.'));
                    $dest_email = $store->getEmail();

                    $app_name = $this->getApplication()->getName();

                    $layout = $this->getLayout()->loadEmail('booking', 'send_email');
                    $layout->getPartial('content_email')->setData($data);
                    $content = $layout->render();
                    $mail = new Zend_Mail('UTF-8');
                    $mail->setBodyHtml($content);
                    $mail->setFrom($data['email'], $data['name']);
                    $mail->addTo($dest_email, $app_name);
                    $mail->setSubject($app_name." - ".$booking->getName()." - ".$store->getStoreName());
                    $mail->send();

                    $html = array(
                        "success" => 1,
                        "message" => $this->_("Thank you for your request. We'll answer you as soon as possible.")
                    );
                }

            }
            catch(Exception $e) {
                $html = array('error' => 1, 'message' => $e->getMessage());
            }

            $this->_sendHtml($html);
        }
    }

}