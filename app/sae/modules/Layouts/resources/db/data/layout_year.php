<?php
# Layout Year
$default_options = Siberian_Json::encode(array(
    "positionMenu" => "menu-middle",
    "textTransform" => "title-lowcase",
    "title" => "titlehidden",
));

$layout_category = new Application_Model_Layout_Category();
$default_layout_category = $layout_category->find("default", "code");

$datas = array(
    'name' => 'Popup menu',
    'category_id' => $default_layout_category->getId(),
    'visibility' => Application_Model_Layout_Homepage::VISIBILITY_HOMEPAGE,
    'code' => 'layout_siberian_year',
    'preview' => '/customization/layout/homepage/layout_year.png',
    'preview_new' => '/customization/layout/homepage/layout_year_modal.png',
    'use_more_button' => 1,
    'use_horizontal_scroll' => 0,
    'number_of_displayed_icons' => 6,
    'position' => "bottom",
    "order" => 6,
    "can_uninstall" => 1,
    "is_active" => 1,
    "use_subtitle" => 1,
    "use_homepage_slider" => 0,
    "options" => $default_options,
);

if(method_exists("Siberian_Feature", "installApplicationLayout")) {
    Siberian_Feature::installApplicationLayout($datas, "default");
} else {
    if(!function_exists("installApplicationLayout")) {
        function installApplicationLayout($datas, $category_code = "custom") {
            $category_model = new Application_Model_Layout_Category();
            $category = $category_model->find($category_code, "code");

            if(empty($datas["category_id"])) {
                $datas["category_id"] = $category->getId();
            }

            $layout = new Application_Model_Layout_Homepage();
            $layout
                ->setData($datas)
                ->insertOrUpdate(array("code"));
        }
    }

    installApplicationLayout($datas, "default");
}