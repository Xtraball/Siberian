<?php 

class Backoffice_View_Header extends Core_View_Default {
    
    protected function _canAccessDiscounts() {
        return $this->isPe() AND version_compare("3.5.0", Siberian_Version::VERSION) <= 0;
    }
   
    protected function _canAccessPreviewer() {
        return Installer_Model_Installer::hasModule("Previewer");
    }

}