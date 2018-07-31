<?php
# Layout 18
$default_options = Siberian_Json::encode([
    "borders" => "border-right",
    "label" => "label-left",
    "textTransform" => "title-lowcase",
]);

$layout_category = new Application_Model_Layout_Category();
$default_layout_category = $layout_category->find("default", "code");

$datas = [
    'name' => 'Layout 18',
    'category_id' => $default_layout_category->getId(),
    'visibility' => Application_Model_Layout_Homepage::VISIBILITY_HOMEPAGE,
    'code' => 'layout_siberian_18',
    'preview' => '/customization/layout/homepage/layout_18.png',
    'preview_new' => '/customization/layout/homepage/layout_18_modal.png',
    'use_more_button' => 1,
    'use_horizontal_scroll' => 0,
    'number_of_displayed_icons' => 9,
    'position' => "bottom",
    "order" => 4,
    "can_uninstall" => 1,
    "is_active" => 1,
    "use_subtitle" => 1,
    "use_homepage_slider" => 1,
    "options" => $default_options,
];

Siberian_Feature::installApplicationLayout($datas, "default");