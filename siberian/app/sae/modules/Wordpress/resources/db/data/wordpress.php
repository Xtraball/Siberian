<?php
$option = (new Application_Model_Option())
    ->find('wordpress', 'code');

if ($option->getId()) {
    $name = "Wordpress";

    # Icons
    $icons = [
        '/wordpress/wordpress1.png',
    ];

    $result = Siberian_Feature::installIcons($name, $icons);

    # Install the Feature
    $data = [
        'library_id' => $result["library_id"],
        'icon_id' => $result["icon_id"],
        'code' => "wordpress",
        'name' => $name,
        'model' => "Wordpress_Model_Wordpress",
        'desktop_uri' => "wordpress/application/",
        'mobile_uri' => "wordpress/mobile_list/",
        'only_once' => 0,
        'is_ajax' => 1,
        'position' => 170,
        'social_sharing_is_available' => 1,
        'backoffice_description' => 'This feature is disabled by default since update 4.13.14 and is deprecated in favor of WordPress v2.'
    ];

    $option = Siberian_Feature::install("integration", $data, ['code']);

    # Layouts
    $layout_data = [
        1, 2, 3
    ];
    $slug = "wordpress";

    Siberian_Feature::installLayouts($option->getId(), $slug, $layout_data);

    # Icons Flat
    $icons = [
        '/wordpress/wordpress1-flat.png',
        '/wordpress/wordpress2-flat.png',
    ];

    Siberian_Feature::installIcons("{$name}-flat", $icons);

    // Disable only not done yet!
    // deprecated from version 4.12.24
    if (__get('wordpress_v1_deprecated') !== 'done') {
        $option
            ->setIsEnabled(0)
            ->save();

        __set('wordpress_v1_deprecated', 'done');
    }
} else {
    // Do not install anymore WordPress v1 feature (but still db scheme), if new install!
}