<?php

class Core_Model_Default extends Core_Model_Default_Abstract {

    public function getApplication() {
        return Application_Model_Application::getInstance();
    }
}

