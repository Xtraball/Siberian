<?php

use Siberian\Json;
use Siberian\Layout;

/**
 * Class Booking_Mobile_ViewController
 */
class Booking_Mobile_ViewController extends Application_Controller_Mobile_Default
{

    /**
     * Fetch the current booking information
     *
     * Only for a refresh purpose.
     *
     */
    public function findAction()
    {

        try {

            if ($value_id = $this->getRequest()->getParam('value_id')) {

                $optionValue = $this->getCurrentOptionValue();

                $booking = $this->getCurrentOptionValue()->getObject();
                $data = ["stores" => []];

                if ($booking->getId()) {

                    $store = new Booking_Model_Store();
                    $stores = $store->findAll([
                        "booking_id" => $booking->getId()
                    ]);

                    foreach ($stores as $store) {
                        $data["stores"][] = [
                            "id" => $store->getId(),
                            "name" => $store->getStoreName()
                        ];
                    }
                }

                $data["page_title"] = $optionValue->getTabbarName();

                try {
                    $settings = Json::decode($optionValue->getSettings());
                } catch (\Exception $e) {
                    $settings = [
                        "design" => "list",
                        "date_format" => "MM/DD/YYYY HH:mm"
                    ];
                }

                // Cover & description!
                $settings["cover"] = empty($booking->getCover()) ?
                    false : $booking->getCover();
                $settings["description"] = empty($booking->getDescription()) ?
                    false : $booking->getDescription();
                $settings["datepicker"] = $booking->getDatepicker();

                $data["settings"] = $settings;

            } else {
                throw new \Siberian\Exception("The value_id is required.");
            }

        } catch (Exception $e) {
            $data = [
                "error" => true,
                "message" => __("Booking::findAction An unknown error occurred, please try again later."),
                "exceptionMessage" => $e->getMessage()
            ];
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
    public function postAction()
    {

        try {

            if ($data = Siberian_Json::decode($this->getRequest()->getRawBody())) {

                $errors = [];

                if (empty($data["name"])) {
                    $errors[] = p__("booking", "Name");
                }

                if ((empty($data["email"]) && empty($data["phone"])) ||
                    (!empty($data["email"]) && !Zend_Validate::is($data["email"], "emailAddress")) && !empty($data["phone"])) {

                    $errors[] = p__("booking", "Phone and/or E-mail");
                }

                if (empty($data["store"])) {
                    $errors[] = p__("booking", "Location");
                }

                if (empty($data["people"])) {
                    $errors[] = p__("booking", "Number of people");
                }

                if (array_key_exists("checkIn", $data) && array_key_exists("checkOut", $data)) {
                    if (empty($data["checkIn"])) {
                        $errors[] = p__("booking", "Checkin");
                    }

                    if (empty($data["checkOut"])) {
                        $errors[] = p__("booking", "Checkout");
                    }
                } else {
                    if (empty($data["date"])) {
                        $errors[] = p__("booking", "Date");
                    }
                }

                if (empty($data["prestation"])) {
                    $errors[] = p__("booking", "Booking details");
                }

                if (!empty($errors)) {
                    $message = __("Please fill out the following fields") . "<br />-&nbsp;";
                    $message .= join("<br />-&nbsp;", $errors);

                    $data = [
                        "error" => true,
                        "errorLines" => $errors,
                        "message" => $message
                    ];

                } else {
                    $store = new Booking_Model_Store();
                    $store->find($data["store"], "store_id");
                    if (!$store->getId()) {
                        throw new Siberian_Exception(__("An error occurred during process.<br />Please try again later."));
                    }
                    $data["location"] = $store->getStoreName();

                    $booking = new Booking_Model_Booking();
                    $booking->find($store->getBookingId(), "booking_id");
                    if (!$booking->getId()) {
                        throw new \Siberian\Exception(__("An error occurred during process.<br />Please try again later."));
                    }
                    $dest_email = $store->getEmail();

                    $app_name = $this->getApplication()->getName();
                    $optionValue = $this->getCurrentOptionValue();

                    try {
                        // E-Mail the app owner!
                        $subject = sprintf("%s - %s - %s",
                            $app_name, $optionValue->getTabbarName(), $store->getStoreName());


                        $baseEmail = $this->baseEmail("send_email", $subject, "", false);

                        foreach ($data as $key => $value) {
                            $baseEmail->setContentFor('content_email', $key, $value);
                        }

                        $content = $baseEmail->render();

                        $mail = new \Siberian_Mail();
                        $mail->setBodyHtml($content);
                        $mail->setFrom($data["email"], $data["name"]);
                        $mail->addTo($dest_email, $app_name);
                        $mail->setSubject($subject);
                        $mail->send();
                    } catch (\Exception $e) {
                        // Something went wrong with the-mail!
                    }

                    try {
                        // E-Mail back the user!
                        $subject = sprintf("%s - %s",
                            $optionValue->getTabbarName(), $store->getStoreName());


                        $baseEmail = $this->baseEmail("send_email_user", $subject, "", false);

                        foreach ($data as $key => $value) {
                            $baseEmail->setContentFor('content_email', $key, $value);
                        }

                        $content = $baseEmail->render();

                        $mail = new \Siberian_Mail();
                        $mail->setBodyHtml($content);
                        $mail->setFrom($dest_email, $app_name);
                        $mail->addTo($data["email"], $data["name"]);
                        $mail->setSubject($subject);
                        $mail->send();
                    } catch (\Exception $e) {
                        // Something went wrong with the-mail!
                    }

                    $data = [
                        "success" => true,
                        "message" => p__("booking","Thank you for your request.<br />We'll answer you as soon as possible.")
                    ];
                }


            } else {
                throw new \Siberian\Exception("The sent request is empty.");
            }

        } catch (Exception $e) {

            $message = $e->getMessage();
            $message = (empty($message)) ? __("%s An unknown error occurred, please try again later.", "Booking::postAction") : $message;

            $data = [
                "error" => true,
                "message" => $message,
                "exceptionMessage" => $e->getMessage()
            ];

        }

        $this->_sendJson($data);


    }

    /**
     * @param $nodeName
     * @param $title
     * @param $message
     * @param $showLegals
     * @return Siberian_Layout|Siberian_Layout_Email
     * @throws Zend_Layout_Exception
     */
    public function baseEmail($nodeName,
                              $title,
                              $message = '',
                              $showLegals = false)
    {
        $layout = new Siberian\Layout();
        $layout = $layout->loadEmail('booking', $nodeName);
        $layout
            ->setContentFor('base', 'email_title', $title)
            ->setContentFor('content_email', 'message', $message)
            ->setContentFor('footer', 'show_legals', $showLegals);

        return $layout;
    }

}
