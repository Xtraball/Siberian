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

                if(empty($data['name'])) $errors[] = __('Your name');
                if((empty($data['email']) OR !Zend_Validate::is($data['email'], 'emailAddress')) && (empty($data['phone']))) $errors[] = __('Your phone number or email address');
                if(empty($data['store'])) $errors[] = __('Location');
                if(empty($data['people'])) $errors[] = __('The number of people');
                if(empty($data['date'])) $errors[] = __('The date and time of the booking');
                if(empty($data['prestation'])) $errors[] = __('The booking information');

                if(!empty($errors)) {
                    $message = __('Please fill out the following fields: ');
                    $message .= join(' - ', $errors);
                    $html = array('error' => 1, 'message' => $message);
                }
                else {
                    $store = new Booking_Model_Store();
                    $store->find($data['store'], 'store_id');
                    if(!$store->getId()) throw new Exception(__('An error occurred during process. Please try again later.'));
                    $data["location"] = $store->getStoreName();

                    $new_date = new Zend_Date();

                    // Replace unknown timezone with server timezone, as it's a booking date
                    $date_str = preg_replace("/-00:00$/", $new_date->get(Zend_Date::GMT_DIFF_SEP), $data["date"]);

                    $data["date"] = $new_date->set($date_str);

                    //vérif value
                    $booking = new Booking_Model_Booking();
                    $booking->find($store->getBookingId(), 'booking_id');
                    if(!$booking->getId()) throw new Exception(__('An error occurred during process. Please try again later.'));
                    $dest_email = $store->getEmail();

                    $app_name = $this->getApplication()->getName();

                    $layout = $this->getLayout()->loadEmail('booking', 'send_email');
                    $layout->getPartial('content_email')->setData($data);
                    $content = $layout->render();

                    # @version 4.8.7 - SMTP
                    $mail = new Siberian_Mail();
                    $mail->setBodyHtml($content);
                    $mail->setFrom($data['email'], $data['name']);
                    $mail->addTo($dest_email, $app_name);
                    $mail->setSubject($app_name." - ".$booking->getName()." - ".$store->getStoreName());
                    $mail->send();

                    $html = array(
                        "success" => 1,
                        "message" => __("Thank you for your request. We'll answer you as soon as possible.")
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
