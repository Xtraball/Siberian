<?php

class Push_AdminController extends Admin_Controller_Default {

    public function globalAction() {
        $this->loadPartials();
    }


    /**
     * Send a global push message
     */
    public function sendAction() {
        $values = $this->getRequest()->getPost();

        $form = new Push_Form_Global();
        if($form->isValid($values)) {

            # Filter checked applications
            $values["checked"] = explode(',', $values["checked"]);

            $values["base_url"] = $this->getRequest()->getBaseUrl();

            $push_global = new Push_Model_Message_Global();
            $result = $push_global->createInstance($values);

            $data = array(
                "success" => true,
                "message" => ($result) ? __("Push message is sent.") : __("No message sent, there is no available applications."),
            );
        } else {
            /** Do whatever you need when form is not valid */

            $data = array(
                "error"     => 1,
                "message"   => $form->getTextErrors(),
                "errors"    => $form->getTextErrors(true),
            );
        }

        $this->_sendJson($data);
    }

}