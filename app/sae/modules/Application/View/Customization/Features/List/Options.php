<?php

class Application_View_Customization_Features_List_Options extends Core_View_Default {

    protected $_icon_color;

    protected function getIconUrl($option) {

        $icon_url = $option->getIconUrl();

        if(!$option->getImage()->getId() OR $option->getImage()->getCanBeColorized()) {

            if(!$this->_icon_color) {
                $this->_initIconColor();
            }

            $icon_url = $this->getColorizedImage($icon_url, $this->_icon_color);
        }

        return $icon_url;

    }

    protected function _initIconColor() {

        $this->_icon_color = "#FFFFFF";
        if(Installer_Model_Installer::hasModule("Whitelabel")) {
            $this->_icon_color = $this->getBlock("area")->getColor();
        }

        return $this;

    }

}
