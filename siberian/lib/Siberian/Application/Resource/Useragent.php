<?php

require_once 'Zend/Application/Resource/Useragent.php';

class Siberian_Application_Resource_UserAgent extends Zend_Application_Resource_UserAgent
{

    /**
     * Intialize resource
     *
     * @return Zend_Http_UserAgent
     */
    public function init()
    {
        return $this->getUserAgent();
    }

}
