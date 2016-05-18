<?php
$library = new Media_Model_Library();
$library->setName('Contact')->save();

$icon_paths = array(
    '/contact/contact1.png',
    '/contact/contact2.png',
    '/contact/contact3.png',
    '/contact/contact4.png',
    '/contact/contact5.png',
    '/contact/contact6.png',
    '/contact/contact7.png',
    '/contact/contact8.png',
    '/contact/contact9.png',
    '/contact/contact10.png',
    '/contact/contact11.png'
);

$icon_id = 0;
foreach($icon_paths as $key => $icon_path) {
    $datas = array('library_id' => $library->getId(), 'link' => $icon_path, 'can_be_colorized' => 1);
    $image = new Media_Model_Library_Image();
    $image->setData($datas)->save();

    if($key == 0) $icon_id = $image->getId();
}


$category = new Application_Model_Option_Category();
$category->find("contact", "code");

$data = array(
    'category_id' => $category->getId(),
    'library_id' => $library->getId(),
    'icon_id' => $icon_id,
    'code' => 'contact',
    'name' => 'Contact',
    'model' => 'Contact_Model_Contact',
    'desktop_uri' => 'contact/application/',
    'mobile_uri' => 'contact/mobile_view/',
    'only_once' => 0,
    'is_ajax' => 1,
    'position' => 120
);
$option = new Application_Model_Option();
$option->setData($data)->save();
