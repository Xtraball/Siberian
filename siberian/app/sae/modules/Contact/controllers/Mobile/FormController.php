<?php

class Contact_Mobile_FormController extends Application_Controller_Mobile_Default
{

    public function indexAction() {
        $this->forward('index', 'index', 'Front', $this->getRequest()->getParams());
    }

    public function templateAction() {
        $this->loadPartials($this->getFullActionName('_').'_l'.$this->_layout_id, false);
    }

    public function postAction() {

        try {

            if($datas = Siberian_Json::decode($this->getRequest()->getRawBody())) {

                try {

                    // Test les eventuelles erreurs
                    $errors = array();
                    if(empty($datas['email']) OR !Zend_Validate::is($datas['email'], 'emailAddress')) {
                        throw new Siberian_Exception(__("Please enter a valid email address"));
                    }

                    $contact = $this->getCurrentOptionValue()->getObject();
                    if(!$contact->getId()) {
                        throw new Siberian_Exception(__("An error occurred while sending your request. Please try again later."));
                    }

                    $dest_email = $contact->getEmail();

                    $app_name = $this->getApplication()->getName();

                    $layout = $this->getLayout()->loadEmail('contact', 'send_email');
                    $layout->getPartial('content_email')->setData($datas);
                    $content = $layout->render();

                    # @version 4.8.7 - SMTP
                    $mail = new Siberian_Mail();
                    $mail->setBodyHtml($content);
                    $mail->setFrom($datas['email'], $datas['name']);
                    $mail->addTo($dest_email, $app_name);
                    $mail->setSubject(__("Message from your app %s", $app_name));
                    $mail->send();

                    $data = array(
                        "success" => true,
                        "message" => __("Your message has been sent")
                    );

                } catch(Exception $e) {
                    $data = array(
                        "error"     => true,
                        "message"   => $e->getMessage()
                    );
                }

            } else {
                throw new Siberian_Exception("The sent request is empty.");
            }
        }
        catch(Exception $e) {

            $data = array(
                "error"                 => true,
                "message"               => __("%s An unknown error occurred, please try again later.", "Contact::postAction"),
                "exceptionMessage"      => $e->getMessage()
            );

        }

        $this->_sendJson($data);
    }
}