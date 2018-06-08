<?php

/**
 * Class Core_Model_Default
 */
class Core_Model_Default extends Core_Model_Default_Abstract
{
    /**
     * @return Application_Model_Application
     */
    public function getApplication()
    {
        return Application_Model_Application::getInstance();
    }
}

