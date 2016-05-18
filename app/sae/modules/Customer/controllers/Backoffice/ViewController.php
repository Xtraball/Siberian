<?php

class Customer_Backoffice_ViewController extends Backoffice_Controller_Default
{

    public function loadAction() {

        $html = array(
            "title" => $this->_("Customer"),
            "icon" => "fa-user",
        );

        $this->_sendHtml($html);

    }

    public function findAction() {

    }

}
