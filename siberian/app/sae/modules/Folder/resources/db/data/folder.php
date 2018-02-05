<?php
$option = (new Application_Model_Option())
    ->find('folder', 'code');
if ($option->getId()) {
    $name = "Folders";
    $category = "misc";

    # Install icons
    $icons = array(
        '/folders/folder1.png',
        '/folders/folder2.png',
        '/folders/folder3.png',
        '/folders/folder4.png',
        '/folders/folder5.png'
    );

    $result = Siberian_Feature::installIcons($name, $icons);

    # Install the Feature
    $data = array(
        'library_id' => $result["library_id"],
        'icon_id' => $result["icon_id"],
        'code' => "folder",
        'name' => "Folder",
        'model' => "Folder_Model_Folder",
        'desktop_uri' => "folder/application/",
        'mobile_uri' => "folder/mobile_list/",
        'only_once' => 0,
        'is_ajax' => 1,
        'position' => 180,
        'backoffice_description' => 'This feature is disabled by default since update 4.12.24 and is deprecated in favor of Folders v2.'
    );

    $option = Siberian_Feature::install($category, $data, ['code']);

    # Layouts
    $layout_data = array(1, 2, 3, 4);
    $slug = "folder";

    Siberian_Feature::installLayouts($option->getId(), $slug, $layout_data);

    # Icons Flat
    $icons = array(
        '/folders/folder1-flat.png',
        '/folders/folder2-flat.png',
        '/folders/folder3-flat.png',
    );

    Siberian_Feature::installIcons("{$name}-flat", $icons);

    // Disable only not done yet!
    // deprecated from version 4.12.24
    if (__get('folder_v1_deprecated') !== 'done') {
        $option
            ->setIsEnabled(0)
            ->save();

        __set('folder_v1_deprecated', 'done');
    }
} else {
    // Do not install anymore Folders v1 feature (but still db scheme), if new install!
}
