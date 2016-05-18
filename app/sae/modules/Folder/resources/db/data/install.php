<?php
$library = new Media_Model_Library();
$library->setName('Folders')->save();

$icon_paths = array(
    '/folders/folder1.png',
    '/folders/folder2.png',
    '/folders/folder3.png',
    '/folders/folder4.png',
    '/folders/folder5.png'
);

$icon_id = 0;
foreach($icon_paths as $key => $icon_path) {
    $datas = array('library_id' => $library->getId(), 'link' => $icon_path, 'can_be_colorized' => 1);
    $image = new Media_Model_Library_Image();
    $image->setData($datas)->save();

    if($key == 0) $icon_id = $image->getId();
}


$category = new Application_Model_Option_Category();
$category->find("misc", "code");

$data = array(
    'category_id' => $category->getId(),
    'library_id' => $library->getId(),
    'icon_id' => $icon_id,
    'code' => "folder",
    'name' => "Folder",
    'model' => "Folder_Model_Folder",
    'desktop_uri' => "folder/application/",
    'mobile_uri' => "folder/mobile_list/",
    'only_once' => 0,
    'is_ajax' => 1,
    'position' => 180
);
$option = new Application_Model_Option();
$option->setData($data)->save();

