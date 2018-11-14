<?php

/**
 * Class Admin_Model_Admin
 *
 * @method Admin_Model_Db_Table_Admin getTable()
 */
class Admin_Model_Admin extends Admin_Model_Admin_Abstract
{

    /**
     * @return Admin_Model_Admin[]
     * @throws Zend_Exception
     */
    public function getSubaccounts()
    {
        if (!$this->_subaccounts) {
            $subaccount = new self();
            $this->_subaccounts = $subaccount->findAll([
                'parent_id' => $this->getId()
            ]);
        }

        return $this->_subaccounts;
    }

    /**
     * @return bool
     */
    public function isAllowedToManageTour()
    {
        return $this->getId() ?
            (bool)$this->getTable()->isAllowedToManageTour($this->getId()) : false;
    }
}