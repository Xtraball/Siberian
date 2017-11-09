<?php 

class Admin_View_Header_Menu extends Admin_View_Default {
    
    protected function _canAccessInvoice() {
        return Siberian_Version::TYPE == "PE" && $this->_canAccess("sales_invoice");
    }

}