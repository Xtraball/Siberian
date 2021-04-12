<?php
$name = "Images";
$category = "media";

# Install icons
$icons = array(
    '/images/image1.png',
    '/images/image2.png',
    '/images/image3.png',
    '/images/image4.png',
    '/images/image5.png',
    '/images/image6.png',
    '/images/image7.png',
);

$result = Siberian_Feature::installIcons($name, $icons);

# Install the Feature
$data = array(
    'library_id' => $result["library_id"],
    'icon_id' => $result["icon_id"],
    'code' => "image_gallery",
    'name' => $name,
    'model' => "Media_Model_Gallery_Image",
    'desktop_uri' => "media/application_gallery_image/",
    'mobile_uri' => "media/mobile_gallery_image_list/",
    "mobile_view_uri" => "media/mobile_gallery_image_view/",
    "mobile_view_uri_parameter" => "gallery_id,offset/0",
    'only_once' => 0,
    'is_ajax' => 1,
    'position' => 90
);

$option = Siberian_Feature::install($category, $data, array('code'));

# Icons Flat
$icons = array(
    '/images/image1-flat.png',
    '/images/image2-flat.png',
    '/images/image3-flat.png',
);

Siberian_Feature::installIcons("{$name}-flat", $icons);

// Alter
$queries = [
    "ALTER TABLE `media_gallery_image_flickr` CHANGE `position` `position` INT(11) NULL DEFAULT '0';",
    "ALTER TABLE `media_gallery_image_custom` CHANGE `position` `position` INT(11) NULL DEFAULT '0';",
    "ALTER TABLE `media_gallery_image_instagram` CHANGE `position` `position` INT(11) NULL DEFAULT '0';",
    "ALTER TABLE `media_gallery_image_picasa` CHANGE `position` `position` INT(11) NULL DEFAULT '0';",
];

foreach ($queries as $query) {
    try {
        $this->query($query);
    } catch (\Exception $e) {
        // Silent!
    }
}
