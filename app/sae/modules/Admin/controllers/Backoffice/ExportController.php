<?php

class Admin_Backoffice_ExportController extends Backoffice_Controller_Default
{

    public function loadAction() {

        $html = array(
            "title" => $this->_("Export your customers"),
            "icon" => "fa-upload",
        );

        $this->_sendHtml($html);

    }

    public function findallAction() {

    }

}
