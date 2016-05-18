<?php

class Application_View_Customization_Features_Edit_Tabbareditor extends Application_View_Customization_Features_List_Options {

    protected $_icon_width;
    protected $_icon_height;

    public function getIconWidth() {

        if(!$this->_icon_width) {
            $this->_calcIconSize();
        }

        return $this->_icon_width;

    }

    public function getIconHeight() {

        if(!$this->_icon_height) {
            $this->_calcIconSize();
        }

        return $this->_icon_height;

    }

    protected function _calcIconSize() {

        $width = 150;
        $height = 150;
        $application = $this->getApplication();
        $current_option = $this->getOptionValue();

        if(!$current_option->getFolderCategoryId() AND $application->getLayoutId() == 8) {
            $rect_blocks = array(1,7);
            $num = 7;
            for( $i=1; $i < 10; $i++ ) {
                $num+=7; $rect_blocks[]= $num;
                $num+=6; $rect_blocks[]= $num;
            }

            $actual_position = 0;
            $options = $application->getPages();

            foreach($options as $option) {
                if(!$option->isActive()) continue;
                if($option->getValueId() == $current_option->getValueId()) {
                    break;
                }
                $actual_position++;
            }

            if(in_array($actual_position, $rect_blocks)) {
                $width = 418;
                $height = 206;
            }

        }

        $this->_icon_width = $width;
        $this->_icon_height = $height;

    }

}
