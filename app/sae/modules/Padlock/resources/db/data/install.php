<?php
// Create the gallery
$library = new Media_Model_Library();
$library->setName('Padlock')->save();

// Create the icon
$data = array("library_id" => $library->getId(), 'link' => "/padlock/padlock.png", "can_be_colorized" => 1);
$image = new Media_Model_Library_Image();
$image->setData($data)->save();
$icon_id = $image->getId();


$category = new Application_Model_Option_Category();
$category->find("misc", "code");

$data = array(
    'category_id' => $category->getId(),
    'library_id' => $library->getId(),
    'icon_id' => $icon_id,
    'code' => "padlock",
    'name' => "Padlock",
    'model' => "Padlock_Model_Padlock",
    'desktop_uri' => "padlock/application/",
    'mobile_uri' => "padlock/mobile_view/",
    'only_once' => 0,
    'is_ajax' => 1,
    'position' => 220
);

$option = new Application_Model_Option();
$option->setData($data)->save();

