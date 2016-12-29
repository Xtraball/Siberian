<?php

class Layouts_Form_Layout9Options extends Siberian_Form_Options_Abstract {


    public function init() {
        parent::init();

        /** Bind as a create form */
        self::addClass("create", $this);
        self::addClass("form-layout-options", $this);

        $title = $this->addSimpleRadio("title", __("Display titles"), array(
            "titlevisible" => __("Visible"),
            "titlehidden" => __("Hidden"),
        ));

        $textTransform = $this->addSimpleSelect("textTransform", __("Title case"), array(
            "title-lowcase" => __("Lower case"),
            "title-uppercase" => __("Upper case"),
        ));

        $menuWidthUnit = $this->addSimpleSelect("sidebarWidthUnit", __("Sidebar width"), array(
            "percentage" => __("Dynamic: percentage"),
            "pixel" => __("Fixed: pixels")
        ));

        $menuWidth = $this->addSimpleSlider("sidebarWidth", __("Width"), array(
            "min" => 10,
            "max" => 90,
            "step" => 1,
            "unit" => "%",
        ), true);
        $menuWidth->addClass("sidebar_width");

        $menuWidthPixel = $this->addSimpleSlider("sidebarWidthPixel", __("Width"), array(
            "min" => 10,
            "max" => 500,
            "step" => 1,
            "unit" => "px",
        ), true);
        $menuWidthPixel->addClass("sidebar_width");

        $this->addNav("submit", __("Save"), false, false);

        self::addClass("btn-sm", $this->getDisplayGroup("submit")->getElement(__("Save")));

        $js = <<<JS
<script type="text/javascript">
$(document).ready(function() {
    var updateUnit = function(value) {
        $('.sidebar_width').parents('.sb-form-line').hide();
        switch(value) {
            case 'percentage':
                $('#sidebarWidth').parents('.sb-form-line').show();
                break;
            case 'pixel': default:
                $('#sidebarWidthPixel').parents('.sb-form-line').show();
                break;
        }
    };

    $('#sidebarWidthUnit').on('change', function() {
        updateUnit($(this).val());
    });
    
    updateUnit($(this).val());
    
}); 
</script>
JS;

        $this->addMarkup($js);

    }

}