<?php

class Core_Model_Default extends Core_Model_Default_Abstract {

    public function getApplication() {
        return Application_Model_Application::getInstance();
    }

    /**
     * Zend_Db_Table_Abstract requires rows to have a getPrimaryKey() method.
     *
     * @return mixed
     */
    public function getPrimaryKey()
    {
        $primary_key = $this->getTable()->getPrimaryKey();
        if (is_array($primary_key)) return $primary_key; else return array($primary_key);
    }
}

