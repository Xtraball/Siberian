<?php

class Mcommerce_MobileController extends Application_Controller_Mobile_Default {

    public function viewAction() {
        $html = $this->_prepareHtml();
        if($this->getCurrentOptionValue()->getCode() == 'm_commerce') {
            $html = array_merge($html, array(
                'next_button_title' => $this->_('Cart'),
                'next_button_arrow_is_visible' => 1,
            ));
        }

        $this->_sendHtml($html);

    }

}
