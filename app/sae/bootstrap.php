<?php

class Module_Bootstrap {

    /**
     * @param Bootstrap $bootstrap
     */
    static public function init($bootstrap) {
        self::_initApp($bootstrap);
    }

    /**
     * @param Bootstrap $bootstrap
     */
    static protected function _initApp($bootstrap) {
        if($bootstrap->_request->isApplication()) {
            $bootstrap->_application = $bootstrap->_request->getApplication();
            Application_Model_Application::setSingleton($bootstrap->_application);
        }
    }
}