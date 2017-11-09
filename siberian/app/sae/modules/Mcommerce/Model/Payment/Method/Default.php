<?php

class Mcommerce_Model_Payment_Method_Default extends Mcommerce_Model_Payment_Method_Abstract {

    public function getUrl() {
        return parent::getUrl('mcommerce/mobile_purchase/confirmpost', array('option_value_id' => $this->getMethod()->getCart()->getValueId()));
    }
    
}
