<?php

class Application_View_Customization_Features_List_Options extends Core_View_Default {

    protected $_icon_color;

    protected function getIconUrl($option) {

        $colorizable = true;

        switch($option->getOptionId()) {
            case "customer_account":
                    if($this->getApplication()->getAccountIconId()) {
                        $image = new Media_Model_Library_Image();
                        $image->find($this->getApplication()->getAccountIconId());
                        $icon_url = $image->getUrl();
                        $colorizable = $image->getCanBeColorized();

                        break;
                    }
            case "more_items":
                    if($this->getApplication()->getMoreIconId()) {
                        $image = new Media_Model_Library_Image();
                        $image->find($this->getApplication()->getMoreIconId());
                        $icon_url = $image->getUrl();
                        $colorizable = $image->getCanBeColorized();

                        break;
                    }
            default:
                $colorizable = (!$option->getImage()->getId() OR $option->getImage()->getCanBeColorized());
                $icon_url = $option->getIconUrl();
        }


        if($colorizable) {

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
