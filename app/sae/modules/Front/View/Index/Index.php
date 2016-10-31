<?php

class Front_View_Index_Index extends Front_View_Default {

    public function getFavicon() {

        try {

            $favicon = "";
            if($this->getCurrentWhiteLabelEditor()) {
                $favicon = $this->getCurrentWhiteLabelEditor()->getFaviconUrl();
            }

            if(!$favicon) {
                $favicon = System_Model_Config::getValueFor("favicon");
            }

            if(!$favicon) {
                $favicon = "/favicon.png";
            }

        } catch(Exception $e) {
            $favicon = "/favicon.png";
        }

        return $favicon;
    }

    public function getLogo() {

        try {

            $logo = "";
            if($this->getCurrentWhiteLabelEditor()) {
                $logo = $this->getCurrentWhiteLabelEditor()->getLogoUrl();
            }

            if(!$logo) {
                $logo = System_Model_Config::getValueFor("logo");
            }

            if(!$logo) {
                $logo = $this->getImage("header/logo.png");
            }

        } catch(Exception $e) {
            $logo = $this->getImage("header/logo.png");
        }

        return $logo;

    }

    protected function _canAccessWhiteLabelEditor() {
        return Installer_Model_Installer::hasModule("Whitelabel") && !$this->getCurrentWhiteLabelEditor() && $this->_canAccess("white_label_editor");
    }

}