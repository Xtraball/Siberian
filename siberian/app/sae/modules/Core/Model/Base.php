<?php

namespace Core\Model;

/**
 * Class Core_Model_Default
 */
class Base extends \Core_Model_Default_Abstract
{
    /**
     * @return \Application_Model_Application
     */
    public function getApplication()
    {
        return \Application_Model_Application::getInstance();
    }
}

