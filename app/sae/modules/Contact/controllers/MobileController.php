<?php

class Contact_MobileController extends Application_Controller_Mobile_Default
{

    public function postAction() {

        if($datas = $this->getRequest()->getPost()) {
            try {

                // Test les eventuelles erreurs
                $errors = array();
                if(empty($datas['name'])) $errors[] = __('Your name');
                if(empty($datas['email']) OR !Zend_Validate::is($datas['email'], 'emailAddress')) $errors[] = __('Your email');
                if(empty($datas['info'])) $errors[] = __('Your request');

                $contact = new Contact_Model_Contact();
                $contact->find($this->getCurrentOptionValue()->getId(), 'value_id');
                if(!$contact->getId()) throw new Exception(__('An error occurred while sending your request. Please try again later.'));

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

                if(!empty($errors)) {
                    $message = __('Please enter properly the following fields: <br />');
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