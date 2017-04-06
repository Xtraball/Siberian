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
            $values["checked"] = array_keys(array_filter($values["checked"], function($v) {
                return ($v == true);
            }));

            $values["base_url"] = $this->getRequest()->getBaseUrl();

            $push_global = new Push_Model_Message_Global();
            $push_global->createInstance($values);

            $data = array(
                "success" => true,
                "message" => __("Push message is sent."),
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