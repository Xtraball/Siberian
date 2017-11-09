<?php

class Core_View_Default extends Core_View_Default_Abstract
{
    public function getApplication() {
        return Application_Model_Application::getInstance();
    }
}
