<?php

// Create the gallery
$library = new Media_Model_Library();
$library->setName('Places')->save();

// Create the icons
$icons = array(
    "/places/places1.png",
    "/places/places2.png",
    "/places/places3.png",
    "/places/places4.png"
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

// Create and declare the feature
$category = new Application_Model_Option_Category();
$category->find("misc", "code");

$data = array(
    'category_id' => $category->getId(),
    'library_id' => $library->getId(),
    'icon_id' => $icon_id,
    'code' => 'places',
    'name' => 'Places',
    'model' => 'Cms_Model_Application_Page',
    'desktop_uri' => 'places/application/',
    'mobile_uri' => 'places/mobile_list/',
    'only_once' => 0,
    'is_ajax' => 1,
    'position' => 240,
    'social_sharing_is_available' => 1
);

$option = new Application_Model_Option();
$option->setData($data)->save();

