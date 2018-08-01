<?php
# Layout Year
$default_options = Siberian_Json::encode([
    "positionMenu" => "menu-middle",
    "textTransform" => "title-lowcase",
    "title" => "titlehidden",
]);

$layout_category = new Application_Model_Layout_Category();
$default_layout_category = $layout_category->find("default", "code");

$datas = [
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
];

Siberian_Feature::installApplicationLayout($datas, "default");