<?php
$library = new Media_Model_Library();
$library->setName('Calendar')->save();

$icon_paths = array(
    '/calendar/calendar1.png',
    '/calendar/calendar2.png',
    '/calendar/calendar3.png'
);

$icon_id = 0;
foreach($icon_paths as $key => $icon_path) {
    $datas = array('library_id' => $library->getId(), 'link' => $icon_path, 'can_be_colorized' => 1);
    $image = new Media_Model_Library_Image();
    $image->setData($datas)->save();

    if($key == 0) $icon_id = $image->getId();
}

$category = new Application_Model_Option_Category();
$category->find("events", "code");

$data = array(
    'category_id' => $category->getId(),
    'library_id' => $library->getId(),
    'icon_id' => $icon_id,
    'code' => 'calendar',
    'name' => 'Calendar',
    'model' => 'Event_Model_Event',
    'desktop_uri' => 'event/application/',
    'mobile_uri' => 'event/mobile_list/',
    'mobile_view_uri' => 'event/mobile_view/',
    'mobile_view_uri_parameter' => 'event_id',
    'only_once' => 0,
    'is_ajax' => 1,
    'position' => 200,
    'social_sharing_is_available' => 1
);

$option = new Application_Model_Option();
$option->setData($data)->save();
