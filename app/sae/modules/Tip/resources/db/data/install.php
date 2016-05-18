<?php

// Create the gallery
$library = new Media_Model_Library();
$library->setName('Tip')->save();

// Create the icons
$icons = array(
    "/tip/tip1.png",
    "/tip/tip2.png"
);

$icon_id = null;
foreach($icons as $icon) {
    $data = array('library_id' => $library->getId(), 'link' => $icon, 'can_be_colorized' => 1);
    $image = new Media_Model_Library_Image();
    $image->setData($data)->save();
    if(is_null($icon_id)) {
        $icon_id = $image->getId();
    }
}

// Categorization
$category = new Application_Model_Option_Category();
$category->find("misc", "code");

$data = array(
    'category_id' => $category->getId(),
    'library_id' => $library->getId(),
    'icon_id' => $icon_id,
    'code' => 'tip',
    'name' => 'Tips calculator',
    'model' => 'Tip_Model_Tip',
    'desktop_uri' => 'tip/application/',
    'mobile_uri' => 'tip/mobile_view/',
    'only_once' => 0,
    'is_ajax' => 1,
    'position' => 240,
    'social_sharing_is_available' => 0
);

$option = new Application_Model_Option();
$option->setData($data)->save();
