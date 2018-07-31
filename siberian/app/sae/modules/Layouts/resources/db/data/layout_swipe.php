<?php
# Layout Swipe
$default_options = Siberian_Json::encode([
    "icons" => "default",
    "loop" => 1,
    "angle" => -10,
    "stretch" => 50,
    "depth" => 200,
]);

$layout_category = new Application_Model_Layout_Category();
$default_layout_category = $layout_category->find("default", "code");

$datas = [
    'name' => 'Layout Swipe',
    'category_id' => $default_layout_category->getId(),
    'visibility' => Application_Model_Layout_Homepage::VISIBILITY_HOMEPAGE,
    'code' => 'layout_siberian_swipe',
    'preview' => '/customization/layout/homepage/layout_swipe.png',
    'preview_new' => '/customization/layout/homepage/layout_swipe_modal.png',
    'use_more_button' => 0,
    'use_horizontal_scroll' => 0,
    'number_of_displayed_icons' => 8,
    'position' => "bottom",
    "order" => 5,
    "can_uninstall" => 1,
    "is_active" => 1,
    "use_subtitle" => 1,
    "use_homepage_slider" => 0,
    "options" => $default_options,
];

Siberian_Feature::installApplicationLayout($datas, "default");