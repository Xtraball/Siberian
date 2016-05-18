<?php

class Mcommerce_Mobile_Sales_ConfirmationController extends Mcommerce_Controller_Mobile_Default {

    public function cancelAction() {

        if(!$this->getRequest()->getParam("value_id")) {
            if($this->getApplication()->useIonicDesign()) {
                sleep(1);
                $this->_redirect('mcommerce/mobile_sales_error/index',array("value_id" => $this->getCurrentOptionValue()->getValueId()));
            } else {
                $this->_redirect("/mcommerce/mobile_sales_confirmation/cancel/", array("value_id" => $this->getCurrentOptionValue()->getId()));
            }
        } else {
            $this->forward('cancel', 'index', 'Front', $this->getRequest()->getParams());
        }
    }

    public function confirmAction() {
        if (!$this->getRequest()->getParam("value_id")) {
            if($this->getApplication()->useIonicDesign()) {
                sleep(1);
                $this->_redirect("/mcommerce/mobile_sales_payment/validatepayment", array_merge($this->getRequest()->getParams(), array("value_id" => $this->getCurrentOptionValue()->getId())));
            } else {
                $this->_redirect("/mcommerce/mobile_sales_confirmation/confirm/", array_merge($this->getRequest()->getParams(), array("value_id" => $this->getCurrentOptionValue()->getId())));
            }
        } else {
            $this->forward('confirm', 'index', 'Front', $this->getRequest()->getParams());
        }
    }
}