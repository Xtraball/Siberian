<?php

namespace Core\Model;

/**
 * Class Core_Model_Default
 */
class Base extends \Core_Model_Default_Abstract
{
    /**
     * @return \Application_Model_Application|mixed
     * @throws \Zend_Exception
     */
    public function getApplication()
    {
        return \Application_Model_Application::getInstance();
    }
}

