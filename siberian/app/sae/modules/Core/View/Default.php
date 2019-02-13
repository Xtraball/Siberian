<?php

/**
 * Class Core_View_Default
 */
class Core_View_Default extends Core_View_Default_Abstract
{
    /**
     * @return Application_Model_Application|mixed
     * @throws Zend_Exception
     */
    public function getApplication()
    {
        return Application_Model_Application::getInstance();
    }
}
