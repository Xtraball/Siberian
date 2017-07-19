<?php

class Booking_Mobile_ViewController extends Application_Controller_Mobile_Default {

    /**
     * Fetch the current booking information
     *
     * Only for a refresh purpose.
     *
     */
    public function findAction() {

        try {

            if($value_id = $this->getRequest()->getParam('value_id')) {

                $booking = $this->getCurrentOptionValue()->getObject();
                $data = array("stores" => array());

                if($booking->getId()) {

                    $store = new Booking_Model_Store();
                    $stores = $store->findAll(array(
                        "booking_id" => $booking->getId()
                    ));

                    foreach($stores as $store) {
                        $data["stores"][] = array(
                            "id"    => $store->getId(),
                            "name"  => $store->getStoreName()
                        );
                    }

                }

                $data["page_title"] = $this->getCurrentOptionValue()->getTabbarName();

            } else {
                throw new Siberian_Exception("The value_id is required.");
            }

        } catch(Exception $e) {
            $data = array(
                "error"                 => true,
                "message"               => __("Booking::findAction An unknown error occurred, please try again later."),
                "exceptionMessage"      => $e->getMessage()
            );
        }

        $this->_sendJson($data);

    }

    /**
     * Submit a Booking request, sent via e-mail
     *
     * @todo
     * - Store booking requests in DB
     * - Add web approval system
     * - Allow user to list his booking requests
     *
     */
    public function postAction() {

        try {

            if($data = Siberian_Json::decode($this->getRequest()->getRawBody())) {

                $errors = array();

                if(empty($data["name"])) {
                    $errors[] = __("Name");
                }

                if((empty($data["email"]) && empty($data["phone"])) ||
                    (!empty($data["email"]) && !Zend_Validate::is($data["email"], "emailAddress")) && !empty($data["phone"])) {

                    $errors[] = __("Phone and/or E-mail");
                }

                if(empty($data["store"])) {
                    $errors[] = __("Location");
                }

                if(empty($data["people"])) {
                    $errors[] = __("Number of people");
                }

                if(empty($data["date"])) {
                    $errors[] = __("Date and time");
                }

                if(empty($data["prestation"])) {
                    $errors[] = __("Booking details");
                }

                if(!empty($errors)) {
                    $message = __("Please fill out the following fields")."<br />-&nbsp;";
                    $message .= join("<br />-&nbsp;", $errors);

                    $data = array(
                        "error" => true,
                        "message" => $message
                    );

                }
                else {
                    $store = new Booking_Model_Store();
                    $store->find($data["store"], "store_id");
                    if(!$store->getId()) {
                        throw new Siberian_Exception(__("An error occurred during process.<br />Please try again later."));
                    }
                    $data["location"] = $store->getStoreName();

                    $new_date = new Zend_Date();
                    /**
                     * Replace unknown timezone with server timezone, as it"s a booking date*
                     * @todo This particular date should be sent as-is, however language/date
                     *       and formats are not clearly defined, and are missing specifications.
                     */
                    $date_str = preg_replace("/-00:00$/", $new_date->get(Zend_Date::GMT_DIFF_SEP), $data["date"]);

                    $data["date"] = $new_date->set($date_str);

                    //vérif value
                    $booking = new Booking_Model_Booking();
                    $booking->find($store->getBookingId(), "booking_id");
                    if(!$booking->getId()) {
                        throw new Siberian_Exception(__("An error occurred during process.<br />Please try again later."));
                    }
                    $dest_email = $store->getEmail();

                    $app_name = $this->getApplication()->getName();

                    $layout = $this->getLayout()->loadEmail("booking", "send_email");
                    $layout->getPartial("content_email")->setData($data);
                    $content = $layout->render();

                    # @version 4.8.7 - SMTP
                    $mail = new Siberian_Mail();
                    $mail->setBodyHtml($content);
                    $mail->setFrom($data["email"], $data["name"]);
                    $mail->addTo($dest_email, $app_name);
                    $mail->setSubject($app_name." - ".$booking->getName()." - ".$store->getStoreName());
                    $mail->send();

                    $data = array(
                        "success" => true,
                        "message" => __("Thank you for your request.<br />We'll answer you as soon as possible.")
                    );
                }


            } else {
                throw new Siberian_Exception("The sent request is empty.");
            }

        } catch(Exception $e) {

            $message = $e->getMessage();
            $message = (empty($message)) ? __("%s An unknown error occurred, please try again later.", "Booking::postAction") : $message;

            $data = array(
                "error"                 => true,
                "message"               => $message,
                "exceptionMessage"      => $e->getMessage()
            );

        }

        $this->_sendJson($data);


    }

}
