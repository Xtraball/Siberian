<?php

$media_library = new Media_Model_Library();
$media_library->setName("Code Scan")->save();

$icon_id = null;
$files_icon = new DirectoryIterator(Core_Model_Directory::getBasePathTo("images/library/code_scan"));
foreach($files_icon as $file) {
    if ($file->isDot()) continue;

    $icon = new Media_Model_Library_Image();
    $icon_data = array(
        "library_id" => $media_library->getId(),
        "link" => "/code_scan/".$file->getFilename(),
        "can_be_colorized" => 1
    );
    $icon->setData($icon_data)->save();

    if($icon_id == null) {
        $icon_id = $icon->getId();
    }
}

$category = new Application_Model_Option_Category();
$category->find("misc", "code");

$data = array(
    "category_id" => $category->getId(),
    "library_id" => $media_library->getId(),
    "code" => "code_scan",
    "name" => "Code Scan",
    "model" => "Codescan_Model_Codescan",
    "library_id" => $media_library->getId(),
    "icon_id" => $icon_id,
    "desktop_uri" => "codescan/application/",
    "mobile_uri" => "codescan/mobile_view/",
    "position" => 150
);

$option = new Application_Model_Option();
$option->setData($data)->save();