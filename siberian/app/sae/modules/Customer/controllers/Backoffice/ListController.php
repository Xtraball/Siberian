<?php

class Customer_Backoffice_ListController extends Backoffice_Controller_Default
{

    public function loadAction() {

        $html = array(
            "title" => $this->_("Customers"),
            "icon" => "fa-users",
        );

        $this->_sendHtml($html);

    }

    public function findallAction() {

    }

}
