<?php
// Push Notifications
$library = new Media_Model_Library();
$library->setName('Push')->save();

$icon_paths = array(
    '/push_notifications/push1.png',
    '/push_notifications/push2.png',
    '/push_notifications/push3.png',
    '/push_notifications/push4.png',
    '/push_notifications/push5.png',
    '/loyalty/loyalty6.png',
);

$icon_id = 0;
foreach($icon_paths as $key => $icon_path) {
    $data = array('library_id' => $library->getId(), 'link' => $icon_path, 'can_be_colorized' => 1);
    $image = new Media_Model_Library_Image();
    $image->setData($data)->save();

    if($key == 0) $icon_id = $image->getId();
}

$category = new Application_Model_Option_Category();
$category->find("contact", "code");

$data = array(
    'category_id' => $category->getId(),
    'library_id' => $library->getId(),
    'icon_id' => $icon_id,
    'code' => "push_notification",
    'name' => "Push Notifications",
    'model' => "Push_Model_Message",
    'desktop_uri' => "push/application/",
    'mobile_uri' => "push/mobile_list/",
    'only_once' => 1,
    'is_ajax' => 1,
    'position' => 130
);
$option = new Application_Model_Option();
$option->setData($data)->save();


// In-App Message
$library = new Media_Model_Library();
$library->setName('Messages')->save();

$icon_paths = array(
    '/inapp_messages/inapp1.png'
);

$icon_id = 0;
foreach($icon_paths as $key => $icon_path) {
    $datas = array('library_id' => $library->getId(), 'link' => $icon_path, 'can_be_colorized' => 1);
    $image = new Media_Model_Library_Image();
    $image->setData($datas)->save();

    if($key == 0) $icon_id = $image->getId();
}


$data = array(
    'category_id' => $category->getId(),
    'library_id' => $library->getId(),
    'icon_id' => $icon_id,
    'code' => "inapp_messages",
    'name' => "In-App Messages",
    'model' => "Push_Model_Message",
    'desktop_uri' => "push/application/",
    'mobile_uri' => "push/mobile_list/",
    'only_once' => 1,
    'is_ajax' => 1,
    'position' => 130
);
$option = new Application_Model_Option();
$option->setData($data)->save();

/** @todo replace me with object update. */
$this->query("
    UPDATE `push_delivered_message` SET is_displayed = 1 WHERE is_read = 1 and is_displayed = 0;
");