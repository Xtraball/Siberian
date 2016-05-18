<?php
$library = new Media_Model_Library();
$library->setName('Custom Page')->save();

$icon_paths = array(
    '/custom_page/custom1.png',
    '/loyalty/loyalty6.png',
    '/newswall/newswall1.png',
    '/newswall/newswall2.png',
    '/newswall/newswall3.png',
    '/newswall/newswall4.png',
    '/push_notifications/push1.png',
    '/push_notifications/push2.png',
    '/catalog/catalog6.png',
    '/catalog/catalog8.png',
    '/catalog/catalog9.png',
);

$icon_id = 0;
foreach($icon_paths as $key => $icon_path) {
    $datas = array('library_id' => $library->getId(), 'link' => $icon_path, 'can_be_colorized' => 1);
    $image = new Media_Model_Library_Image();
    $image->setData($datas)->save();

    if($key == 0) $icon_id = $image->getId();
}

$category = new Application_Model_Option_Category();
$category->find("customization", "code");

$datas = array(
    'category_id' => $category->getId(),
    'library_id' => $library->getId(),
    'icon_id' => $icon_id,
    'code' => 'custom_page',
    'name' => 'Custom Page',
    'model' => 'Cms_Model_Application_Page',
    'desktop_uri' => 'cms/application_page/',
    'mobile_uri' => 'cms/mobile_page_view/',
    "mobile_view_uri" => "cms/mobile_page_view/",
    "mobile_view_uri_parameter" => null,
    'only_once' => 0,
    'is_ajax' => 1,
    'position' => 70,
    'social_sharing_is_available' => 1
);
$option = new Application_Model_Option();
$option->setData($datas)->save();

$datas = array(
    array('type' => 'text', 'position' => 1, 'icon' => 'icon-file-alt', 'title' => 'Text', 'template' => 'cms/application/page/edit/block/text.phtml', 'mobile_template' => 'cms/page/%s/view/block/text.phtml'),
    array('type' => 'image', 'position' => 2, 'icon' => 'icon-picture', 'title' => 'Image', 'template' => 'cms/application/page/edit/block/image.phtml', 'mobile_template' => 'cms/page/%s/view/block/image.phtml'),
    array('type' => 'video', 'position' => 3, 'icon' => 'icon-facetime-video', 'title' => 'Video', 'template' => 'cms/application/page/edit/block/video.phtml', 'mobile_template' => 'cms/page/%s/view/block/video.phtml'),
    array('type' => 'address', 'position' => 4, 'icon' => 'icon-location-arrow', 'title' => 'Address', 'template' => 'cms/application/page/edit/block/address.phtml', 'mobile_template' => 'cms/page/%s/view/block/address.phtml'),
    array('type' => 'button', 'position' => 5, 'icon' => 'icon-barcode', 'title' => 'Button', 'template' => 'cms/application/page/edit/block/button.phtml', 'mobile_template' => 'cms/page/%s/view/block/button.phtml'),
    array('type' => 'file', 'position' => 6, 'icon' => 'icon-paper-clip', 'title' => 'Attachment', 'template' => 'cms/application/page/edit/block/file.phtml', 'mobile_template' => 'cms/page/%s/view/block/file.phtml'),
    array('type' => 'slider', 'position' => 7, 'icon' => 'icon-play-circle', 'title' => 'Slider', 'template' => 'cms/application/page/edit/block/slider.phtml', 'mobile_template' => 'cms/page/%s/view/block/slider.phtml')
);


foreach($datas as $data) {
    $block = new Cms_Model_Application_Block();
    $block->setData($data)->save();
}

