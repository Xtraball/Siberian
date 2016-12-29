<?php
$name = "Privacy Policy";
$category = "misc";

# Install icons
$icons = array(
    "/form/form3-flat.png",
);

$result = Siberian_Feature::installIcons($name, $icons);

# Install the Feature
$data = array(
    'library_id'                    => $result["library_id"],
    'icon_id'                       => $result["icon_id"],
    'code'                          => 'privacy_policy',
    'name'                          => $name,
    'model'                         => "System_Model_Config",
    'desktop_uri'                   => 'cms/application_privacypolicy/',
    'mobile_uri'                    => 'cms/privacy_policy/',
    "mobile_view_uri_parameter"     => null,
    'only_once'                     => 1,
    'is_ajax'                       => 1,
    'position'                      => 200,
    'social_sharing_is_available'   => 0
);

$option = Siberian_Feature::install($category, $data, array('code'));

Siberian_Feature::installIcons("{$name}-flat", $icons);