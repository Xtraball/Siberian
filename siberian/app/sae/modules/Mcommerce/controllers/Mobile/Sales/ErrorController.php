<?php

class Mcommerce_Mobile_Sales_ErrorController extends Mcommerce_Controller_Mobile_Default {

    public function templateAction() {
        parent::templateAction();
        $this->getLayout()->getPartial("content")->setMessages($this->getSession()->getMessages());
    }

}