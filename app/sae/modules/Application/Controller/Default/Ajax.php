<?php

class Application_Controller_Default_Ajax extends Application_Controller_Default {

    public function preDispatch() {
        if(!$this->getRequest()->isXmlHttpRequest()) {
            $this->_forward('noroute');
        }
    }

}
