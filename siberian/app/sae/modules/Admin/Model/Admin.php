<?php

class Admin_Model_Admin extends Admin_Model_Admin_Abstract
{
    public function getSubaccounts() {

        if(!$this->_subaccounts) {
            $subaccount = new self();
            $this->_subaccounts = $subaccount->findAll(array('parent_id' => $this->getId()));
        }

        return $this->_subaccounts;

    }

    public function isAllowedToManageTour() {
        return $this->getId() ? (bool)$this->getTable()->isAllowedToManageTour($this->getId()) : false;
    }
}