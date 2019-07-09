<?php

$blocks = [
    [
        "type" => "text",
        "position" => 1,
        "icon" => "icon-file-alt fa-file-text-o",
        "title" => "Text",
        "template" => "cms/application/page/edit/block/text.phtml",
        "mobile_template" => "cms/page/%s/view/block/text.phtml",
    ],
    [
        "type" => "image",
        "position" => 2,
        "icon" => "icon-picture fa-file-image-o",
        "title" => "Image",
        "template" => "cms/application/page/edit/block/image.phtml",
        "mobile_template" => "cms/page/%s/view/block/image.phtml",
    ],
    [
        "type" => "video",
        "position" => 3,
        "icon" => "icon-facetime-video fa-file-video-o",
        "title" => "Video",
        "template" => "cms/application/page/edit/block/video.phtml",
        "mobile_template" => "cms/page/%s/view/block/video.phtml",
    ],
    [
        "type" => "address",
        "position" => 4,
        "icon" => "icon-location-arrow fa-location-arrow",
        "title" => "Address",
        "template" => "cms/application/page/edit/block/address.phtml",
        "mobile_template" => "cms/page/%s/view/block/address.phtml",
    ],
    [
        "type" => "button",
        "position" => 5,
        "icon" => "icon-barcode fa-barcode",
        "title" => "Button",
        "template" => "cms/application/page/edit/block/button.phtml",
        "mobile_template" => "cms/page/%s/view/block/button.phtml",
    ],
    [
        "type" => "file",
        "position" => 6,
        "icon" => "icon-paper-clip fa-paperclip",
        "title" => "Attachment",
        "template" => "cms/application/page/edit/block/file.phtml",
        "mobile_template" => "cms/page/%s/view/block/file.phtml",
    ],
    [
        "type" => "slider",
        "position" => 7,
        "icon" => "icon-play-circle fa-play-circle-o",
        "title" => "Slider",
        "template" => "cms/application/page/edit/block/slider.phtml",
        "mobile_template" => "cms/page/%s/view/block/slider.phtml",
    ],
    [
        "type" => "cover",
        "position" => 8,
        "icon" => "icon-picture fa-picture-o",
        "title" => "Cover",
        "template" => "cms/application/page/edit/block/cover.phtml",
        "mobile_template" => "cms/page/%s/view/block/cover.phtml",
    ],
];

foreach($blocks as $blockData) {
    $block = new Cms_Model_Application_Block();
    $block
        ->setData($blockData)
        ->insertOrUpdate(["type"]);
}

try {
    $this->query('ALTER TABLE cms_application_page_block_button CHANGE type_id  type_id ENUM(\'link\',\'phone\',\'email\') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT \'phone\';');
} catch (\Exception $e) {
    // Skip
}