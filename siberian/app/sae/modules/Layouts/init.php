<?php

/**
 * @param $bootstrap
 */
$init = static function ($bootstrap) {

    $js = [];
    $css = [];

    #===== All layouts assets =====#
    Siberian_Assets::registerAssets("Layouts", "/app/sae/modules/Layouts/resources/var/apps/");
    #===== /All layouts assets =====#


    #===== Layout Swipe =====#
    $js[] = "modules/layout/home/layout_siberian_swipe/swiper/swiper.min.js";
    $js[] = "modules/layout/home/layout_siberian_swipe/hooks.js";
    $css[] = "modules/layout/home/layout_siberian_swipe/swiper/swiper.min.css";
    $css[] = "modules/layout/home/layout_siberian_swipe/style.css";

    Siberian_Feature::registerRatioCallback("layout_siberian_swipe", static function ($position, $options = null) {
        $sizes = [
            "width" => 820,
            "height" => 480,
        ];

        if (!empty($options) &&
            isset($options["icons"]) &&
            ($options["icons"] === "default")) {
            $sizes = [
                "width" => 512,
                "height" => 512,
            ];
        }

        return $sizes;
    });

    Siberian_Feature::registerLayoutOptionsCallbacks("layout_siberian_swipe", "Layouts_Form_SwipeOptions", static function ($datas) {
        return [
            "loop" => $datas["loop"],
            "coverflow" => [
                "rotate" => $datas["rotate"],
                "stretch" => $datas["stretch"],
                "depth" => $datas["depth"],
            ],
        ];
    });
    #===== /Layout Swipe =====#


    #===== Layout Apartments =====#
    Siberian_Feature::registerRatioCallback("layout_17", static function ($position, $options = null) {
        $width = 512;
        $height = 512;

        if (!empty($options) &&
            isset($options["icons"]) &&
            ($options["icons"] === "cover")) {
            switch ($position) {
                default:
                case "0":
                case "11":
                    $width = 512;
                    $height = 512;
                    break;
                case "1":
                case "2":
                case "3":
                case "4":
                case "7":
                case "8":
                case "9":
                case "10":
                    $width = 256;
                    $height = 256;
                    break;
                case "5":
                case "6":
                    $width = 512;
                    $height = 256;
                    break;
            }
        }

        return [
            "width" => $width,
            "height" => $height,
        ];
    });
    Siberian_Feature::registerLayoutOptionsCallbacks("layout_17", "Layouts_Form_ApartmentsOptions", static function ($datas) {
        return [];
    });
    #===== /Layout Apartments =====#


    #===== Layout 18 =====#
    $js[] = "modules/layout/home/layout_siberian_18/hooks.js";
    $css[] = "modules/layout/home/layout_siberian_18/style.css";

    Siberian_Feature::registerLayoutOptionsCallbacks("layout_siberian_18", "Layouts_Form_Layout18Options", static function ($datas) {
        return [
            "borders" => $datas["borders"],
            "label" => $datas["label"],
            "textTransform" => $datas["textTransform"],
        ];
    });
    #===== /Layout 18 =====#


    #===== Layout 1 =====#
    Siberian_Feature::registerLayoutOptionsCallbacks("layout_1", "Layouts_Form_Layout1Options", static function ($datas) {
        return [
            "shadow" => $datas["shadow"],
        ];
    });
    #===== /Layout 1 =====#


    #===== Layout 2 =====#
    Siberian_Feature::registerLayoutOptionsCallbacks("layout_2", "Layouts_Form_Layout2Options", static function ($datas) {
        return [
            "shadow" => $datas["shadow"],
        ];
    });
    #===== /Layout 2 =====#


    #===== Layout 3 - 3H =====#
    Siberian_Feature::registerLayoutOptionsCallbacks("layout_3", "Layouts_Form_Layout3Options", static function ($datas) {
        return [
            "title" => $datas["title"],
        ];
    });

    Siberian_Feature::registerLayoutOptionsCallbacks("layout_3_h", "Layouts_Form_Layout3HorizontalOptions", static function ($datas) {
        return [
            "colorizePager" => false,
        ];
    });
    #===== /Layout 3 - 3H =====#


    #===== Layout 4 =====#
    Siberian_Feature::registerLayoutOptionsCallbacks("layout_4", "Layouts_Form_Layout4Options", static function ($datas) {
        return [
            "title" => $datas["title"],
        ];
    });

    Siberian_Feature::registerLayoutOptionsCallbacks("layout_4_h", "Layouts_Form_Layout4HorizontalOptions", static function ($datas) {
        return [
            "colorizePager" => false,
        ];
    });
    #===== /Layout 4 =====#


    #===== Layout 5 =====#
    Siberian_Feature::registerLayoutOptionsCallbacks("layout_5", "Layouts_Form_Layout5Options", static function ($datas) {
        return [
            "textTransform" => $datas["textTransform"],
        ];
    });

    Siberian_Feature::registerLayoutOptionsCallbacks("layout_5_h", "Layouts_Form_Layout5HorizontalOptions", static function ($datas) {
        return [
            "colorizePager" => false,
        ];
    });
    #===== /Layout 5 =====#


    #===== Layout 6 =====#
    Siberian_Feature::registerLayoutOptionsCallbacks("layout_6", "Layouts_Form_Layout6Options", static function ($datas) {
        return [
            "label" => $datas["label"],
            "textTransform" => $datas["textTransform"],
        ];
    });
    #===== /Layout 6 =====#


    #===== Layout 7 =====#
    Siberian_Feature::registerLayoutOptionsCallbacks("layout_7", "Layouts_Form_Layout7Options", static function ($datas) {
        return [
            "borders" => $datas["borders"],
            "textTransform" => $datas["textTransform"],
            "title" => $datas["title"],
        ];
    });
    #===== /Layout 7 =====#


    #===== Layout 9 =====#
    Siberian_Feature::registerLayoutOptionsCallbacks("layout_9", "Layouts_Form_Layout9Options", static function ($datas) {
        return [
            "background" => $datas["background"],
            "textTransform" => $datas["textTransform"],
            "title" => $datas["title"],
        ];
    });
    #===== /Layout 9 =====#


    #===== Layout 10 =====#
    Siberian_Feature::registerLayoutOptionsCallbacks("layout_10", "Layouts_Form_Layout10Options", static function ($datas) {
        return [
            "border" => $datas["border"],
            "shadow" => $datas["shadow"],
        ];
    });
    #===== /Layout 10 =====#

    #===== Layout Year =====#
    $js[] = "modules/layout/home/layout_siberian_year/hooks.js";
    $css[] = "modules/layout/home/layout_siberian_year/style.css";

    Siberian_Feature::registerLayoutOptionsCallbacks("layout_siberian_year", "Layouts_Form_LayoutYearOptions", static function ($datas) {
        $options = [
            "positionMenu" => $datas["menu-middle"],
            "textTransform" => $datas["textTransform"],
            "title" => $datas["titlehidden"],
        ];

        return $options;
    });
    #===== /Layout Year =====#

    #===== All layouts css/js =====#
    Siberian_Assets::addJavascripts($js);
    Siberian_Assets::addStylesheets($css);
    #===== /All layouts css/js =====#

    // Hotpatch ACL
    $layoutsAcl = __get('layout_acl_4.20.9_patch');
    if ($layoutsAcl !== 'done') {
        // 4.20.9 installing Layouts ACLs
        try {
            $designResource = (new \Acl_Model_Resource())->find('editor_design_layout', 'code');
            if ($designResource && $designResource->getId()) {
                $layouts = (new Application_Model_Layout_Homepage())->findAll();
                foreach ($layouts as $layout) {
                    $code = $layout->getCode();
                    $name = $layout->getName();

                    // Create or update
                    $resource = new \Acl_Model_Resource();
                    $resource
                        ->setData(
                            [
                                'parent_id' => $designResource->getId(),
                                'code' => 'layout_' . $code,
                                'label' => $name,
                            ]
                        )
                        ->insertOrUpdate(['code']);
                }
                __set('layout_acl_4.20.9_patch', 'done');
            }
            // Abort if something is wrong!
        } catch (\Exception $e) {
            // Silently fails!
        }
    }
};
