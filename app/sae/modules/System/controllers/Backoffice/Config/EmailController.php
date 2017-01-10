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

}
