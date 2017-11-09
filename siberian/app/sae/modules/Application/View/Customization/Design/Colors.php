<?php

class Application_View_Customization_Design_Colors extends Admin_View_Default {

    public function getClassFor($block, $element) {

        $offset = 0;
        $class_names = array("col-md-3");

        switch($element) {
            case "color": /* Not used */ break;
            case "background_color":
                $offset = 3;
                if($block->getColorVariableName()) {
                    $offset = 0;
                }
                break;
            case "border_color":
                $offset = 9;
                if($block->getColorVariableName()) {
                    $offset = 3;
                }
                if($block->getBackgroundColorVariableName()) {
                    $offset = 0;
                }
                break;
            case "image_color":
                $offset = 9;
                if($block->getColorVariableName()) {
                    $offset = 6;
                }
                if($block->getBackgroundColorVariableName()) {
                    $offset = 3;
                }
                if($block->getBorderColorVariableName()) {
                    $offset = 0;
                }
                break;
        }

        if($offset > 0) {
            $class_names[] = "col-md-offset-".$offset;
        }

        $class_names[] = "text-center";

        return join(" ", $class_names);

    }

}
