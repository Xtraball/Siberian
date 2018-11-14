<?php

/**
 * Class Admin_View_Header_Menu
 */
class Admin_View_Header_Menu extends Admin_View_Default
{
    /**
     * @return bool
     */
    protected function _canAccessInvoice()
    {
        return Siberian_Version::TYPE == 'PE' && $this->_canAccess('sales_invoice');
    }

    /**
     * @return bool
     */
    protected function _canAccessSubscription()
    {
        return Siberian_Version::TYPE == 'PE' && $this->_canAccess('sales_subscription');
    }
}