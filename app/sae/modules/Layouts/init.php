<?php
$init = function($bootstrap) {

    $js = array();
    $css = array();

    #===== All layouts assets =====#
    Siberian_Assets::registerAssets("Layouts", "/app/sae/modules/Layouts/resources/var/apps/");
    #===== /All layouts assets =====#


    #===== Layout Swipe =====#
    $js[] = "modules/layout/home/layout_siberian_swipe/swiper/swiper.min.js";
    $js[] = "modules/layout/home/layout_siberian_swipe/hooks.js";
    $css[] = "modules/layout/home/layout_siberian_swipe/swiper/swiper.min.css";
    $css[] = "modules/layout/home/layout_siberian_swipe/style.css";

    Siberian_Feature::registerRatioCallback("layout_siberian_swipe", function($position, $options = null) {
        $sizes = array(
            "width" => 820,
            "height" => 480,
        );

        if(!empty($options)) {
            if(isset($options["icons"]) && ($options["icons"] == "default")) {
                $sizes = array(
                    "width" => 512,
                    "height" => 512,
                );
            }
        }

        return $sizes;
    });

    Siberian_Feature::registerLayoutOptionsCallbacks("layout_siberian_swipe", "Layouts_Form_SwipeOptions", function($datas) {
        $options = array(
            "loop" => $datas["loop"],
            "coverflow" => array(
                "rotate" => $datas["rotate"],
                "stretch" => $datas["stretch"],
                "depth" => $datas["depth"],
            ),
        );

        return $options;
    });
    #===== /Layout Swipe =====#


    #===== Layout Apartments =====#
    Siberian_Feature::registerRatioCallback("layout_17", function($position, $options = null) {
        $width = 512;
        $height = 512;

        if(!empty($options)) {
            if(isset($options["icons"]) && ($options["icons"] == "cover")) {
                switch($position) {
                    default: case "0": case "11":
                        $width = 512;
                        $height = 512;
                        break;
                    case "1": case "2": case "3": case "4":
                    case "7": case "8": case "9": case "10":
                        $width = 256;
                        $height = 256;
                        break;
                    case "5": case "6":
                        $width = 512;
                        $height = 256;
                        break;
                }
            }
        }

        return array(
            "width" => $width,
            "height" => $height,
        );
    });
    Siberian_Feature::registerLayoutOptionsCallbacks("layout_17", "Layouts_Form_ApartmentsOptions", function($datas) {return array();});
    #===== /Layout Apartments =====#


    #===== Layout 18 =====#
    $js[] = "modules/layout/home/layout_siberian_18/hooks.js";
    $css[] = "modules/layout/home/layout_siberian_18/style.css";

    Siberian_Feature::registerLayoutOptionsCallbacks("layout_siberian_18", "Layouts_Form_Layout18Options", function($datas) {
        $options = array(
            "borders" => $datas["borders"],
            "label" => $datas["label"],
            "textTransform" => $datas["textTransform"],
        );

        return $options;
    });
    #===== /Layout 18 =====#


    #===== Layout 1 =====#
    Siberian_Feature::registerLayoutOptionsCallbacks("layout_1", "Layouts_Form_Layout1Options", function($datas) {
        $options = array(
            "shadow" => $datas["shadow"],
        );

        return $options;
    });
    #===== /Layout 1 =====#


    #===== Layout 2 =====#
    Siberian_Feature::registerLayoutOptionsCallbacks("layout_2", "Layouts_Form_Layout2Options", function($datas) {
        $options = array(
            "shadow" => $datas["shadow"],
        );

        return $options;
    });
    #===== /Layout 2 =====#


    #===== Layout 3 - 3H =====#
    Siberian_Feature::registerLayoutOptionsCallbacks("layout_3", "Layouts_Form_Layout3Options", function($datas) {
        $options = array(
            "title" => $datas["title"],
        );

        return $options;
    });

    Siberian_Feature::registerLayoutOptionsCallbacks("layout_3_h", "Layouts_Form_Layout3HorizontalOptions", function($datas) {
        $options = array(
            "colorizePager" => false,
        );

        return $options;
    });
    #===== /Layout 3 - 3H =====#


    #===== Layout 4 =====#
    Siberian_Feature::registerLayoutOptionsCallbacks("layout_4", "Layouts_Form_Layout4Options", function($datas) {
        $options = array(
            "title" => $datas["title"],
        );

        return $options;
    });

    Siberian_Feature::registerLayoutOptionsCallbacks("layout_4_h", "Layouts_Form_Layout4HorizontalOptions", function($datas) {
        $options = array(
            "colorizePager" => false,
        );

        return $options;
    });
    #===== /Layout 4 =====#


    #===== Layout 5 =====#
    Siberian_Feature::registerLayoutOptionsCallbacks("layout_5", "Layouts_Form_Layout5Options", function($datas) {
        $options = array(
            "textTransform" => $datas["textTransform"],
        );

        return $options;
    });

    Siberian_Feature::registerLayoutOptionsCallbacks("layout_5_h", "Layouts_Form_Layout5HorizontalOptions", function($datas) {
        $options = array(
            "colorizePager" => false,
        );

        return $options;
    });
    #===== /Layout 5 =====#


    #===== Layout 6 =====#
    Siberian_Feature::registerLayoutOptionsCallbacks("layout_6", "Layouts_Form_Layout6Options", function($datas) {
        $options = array(
            "label" => $datas["label"],
            "textTransform" => $datas["textTransform"],
        );

        return $options;
    });
    #===== /Layout 6 =====#


    #===== Layout 7 =====#
    Siberian_Feature::registerLayoutOptionsCallbacks("layout_7", "Layouts_Form_Layout7Options", function($datas) {
        $options = array(
            "borders" => $datas["borders"],
            "textTransform" => $datas["textTransform"],
            "title" => $datas["title"],
        );

        return $options;
    });
    #===== /Layout 7 =====#


    #===== Layout 9 =====#
    Siberian_Feature::registerLayoutOptionsCallbacks("layout_9", "Layouts_Form_Layout9Options", function($datas) {
        $options = array(
            "background" => $datas["background"],
            "textTransform" => $datas["textTransform"],
            "title" => $datas["title"],
        );

        return $options;
    });
    #===== /Layout 9 =====#


    #===== Layout 10 =====#
    Siberian_Feature::registerLayoutOptionsCallbacks("layout_10", "Layouts_Form_Layout10Options", function($datas) {
        $options = array(
            "border" => $datas["border"],
            "shadow" => $datas["shadow"],
        );

        return $options;
    });
    #===== /Layout 10 =====#

    #===== Layout Year =====#
    $js[] = "modules/layout/home/layout_siberian_year/hooks.js";
    $css[] = "modules/layout/home/layout_siberian_year/style.css";

    Siberian_Feature::registerLayoutOptionsCallbacks("layout_siberian_year", "Layouts_Form_LayoutYearOptions", function($datas) {
        $options = array(
            "positionMenu" => $datas["menu-middle"],
            "textTransform" => $datas["textTransform"],
            "title" => $datas["titlehidden"],
        );

        return $options;
    });
    #===== /Layout Year =====#

    #===== All layouts css/js =====#
    Siberian_Assets::addJavascripts($js);
    Siberian_Assets::addStylesheets($css);
    #===== /All layouts css/js =====#
};