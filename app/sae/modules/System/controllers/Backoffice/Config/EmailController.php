<?php

class System_Backoffice_Config_EmailController extends System_Controller_Backoffice_Default {

    protected $_codes = array(
        "support_name",
        "support_email",
        "support_link",
        "support_chat_code",
        "enable_custom_smtp",
        "editor_design"
    );

    public function loadAction() {

        $html = array(
            "title" => __("Communications"),
            "icon" => "fa-exchange",
        );

        $this->_sendHtml($html);

    }

    public function testsmtpAction() {
        $request = $this->getRequest();
        $params = $request->getParams();

        try {
            if(!isset($params["email"])) {
                throw new Siberian_Exception(__("E-mail is required in order to test SMTP"));
            }

            $mail = new Siberian_Mail();
            $mail->setBodyHtml("This is a test e-mail.");
            $mail->addTo($params["email"]);
            $mail->test();

            $data = array(
                "success" => true,
                "message" => __("A e-mail has been sent to %s, please check your inbox.", $params["email"])
            );

        } catch(Exception $e) {
            $data = array(
                "error" => true,
                "message" => $e->getMessage()
            );
        }

        $this->_sendJson($data);
    }

}
