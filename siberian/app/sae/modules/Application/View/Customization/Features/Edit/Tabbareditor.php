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

        $width = 512;
        $height = 512;
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

        $layout_model = new Application_Model_Layout_Homepage();
        $layout = $layout_model->find($application->getLayoutId());
        if(!$current_option->getFolderCategoryId() && Siberian_Feature::getRatioCallback($layout->getCode())) {
            $callback = Siberian_Feature::getRatioCallback($layout->getCode());

            $actual_position = 0;
            $options = $application->getPages();

            foreach($options as $option) {
                if(!$option->isActive()) continue;
                if($option->getValueId() == $current_option->getValueId()) {
                    break;
                }
                $actual_position++;
            }

            $sizes = call_user_func_array($callback, array($actual_position, Siberian_Json::decode($application->getLayoutOptions())));

            if(isset($sizes["width"])) {
                $width = $sizes["width"];
            }
            if(isset($sizes["height"])) {
                $height = $sizes["height"];
            }
        }

        $this->_icon_width = $width;
        $this->_icon_height = $height;

    }

}
