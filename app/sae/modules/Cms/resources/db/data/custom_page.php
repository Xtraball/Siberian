<?php
$name = "Custom Page";
$category = "customization";

# Install icons
$icons = array(
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

$result = Siberian_Feature::installIcons($name, $icons);

# Install the Feature
$data = array(
    'library_id'                    => $result["library_id"],
    'icon_id'                       => $result["icon_id"],
    'code'                          => 'custom_page',
    'name'                          => $name,
    'model'                         => 'Cms_Model_Application_Page',
    'desktop_uri'                   => 'cms/application_page/',
    'mobile_uri'                    => 'cms/mobile_page_view/',
    "mobile_view_uri"               => "cms/mobile_page_view/",
    "mobile_view_uri_parameter"     => null,
    'only_once'                     => 0,
    'is_ajax'                       => 1,
    'position'                      => 70,
    'social_sharing_is_available'   => 1
);

$option = Siberian_Feature::install($category, $data, array('code'));

$datas = array(
    array(
        'type' => 'text',
        'position' => 1,
        'icon' => 'icon-file-alt fa-file-text-o',
        'title' => 'Text',
        'template' => 'cms/application/page/edit/block/text.phtml',
        'mobile_template' => 'cms/page/%s/view/block/text.phtml',
    ),
    array(
        'type' => 'image',
        'position' => 2,
        'icon' => 'icon-picture fa-file-image-o',
        'title' => 'Image',
        'template' => 'cms/application/page/edit/block/image.phtml',
        'mobile_template' => 'cms/page/%s/view/block/image.phtml',
    ),
    array(
        'type' => 'video',
        'position' => 3,
        'icon' => 'icon-facetime-video fa-file-video-o',
        'title' => 'Video',
        'template' => 'cms/application/page/edit/block/video.phtml',
        'mobile_template' => 'cms/page/%s/view/block/video.phtml',
    ),
    array(
        'type' => 'address',
        'position' => 4,
        'icon' => 'icon-location-arrow fa-location-arrow',
        'title' => 'Address',
        'template' => 'cms/application/page/edit/block/address.phtml',
        'mobile_template' => 'cms/page/%s/view/block/address.phtml',
    ),
    array(
        'type' => 'button',
        'position' => 5,
        'icon' => 'icon-barcode fa-barcode',
        'title' => 'Button',
        'template' => 'cms/application/page/edit/block/button.phtml',
        'mobile_template' => 'cms/page/%s/view/block/button.phtml',
    ),
    array(
        'type' => 'file',
        'position' => 6,
        'icon' => 'icon-paper-clip fa-paperclip',
        'title' => 'Attachment',
        'template' => 'cms/application/page/edit/block/file.phtml',
        'mobile_template' => 'cms/page/%s/view/block/file.phtml',
    ),
    array(
        'type' => 'slider',
        'position' => 7,
        'icon' => 'icon-play-circle fa-play-circle-o',
        'title' => 'Slider',
        'template' => 'cms/application/page/edit/block/slider.phtml',
        'mobile_template' => 'cms/page/%s/view/block/slider.phtml',
    ),
    array(
        'type' => 'cover',
        'position' => 8,
        'icon' => 'icon-picture fa-picture-o',
        'title' => 'Cover',
        'template' => 'cms/application/page/edit/block/cover.phtml',
        'mobile_template' => 'cms/page/%s/view/block/cover.phtml',
    ),
);


foreach($datas as $data) {
    $block = new Cms_Model_Application_Block();
    $block
        ->setData($data)
        ->insertOrUpdate(array("type"));
}


# Icons Flat
$icons = array(
    '/custom_page/custom1-flat.png',
    '/custom_page/custom2-flat.png',
    '/custom_page/custom3-flat.png',
);

Siberian_Feature::installIcons("{$name}-flat", $icons);