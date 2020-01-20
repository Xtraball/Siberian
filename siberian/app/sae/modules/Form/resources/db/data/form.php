<?php

$option = (new Application_Model_Option())
    ->find('form', 'code');
if ($option->getId()) {

    $name = "Form";
    $category = "contact";

    # Install icons
    $icons = array(
        '/form/form1.png',
        '/form/form2.png',
        '/form/form3.png',
        '/calendar/calendar1.png',
        '/catalog/catalog6.png',
    );

    $result = Siberian_Feature::installIcons($name, $icons);

    # Install the Feature
    $data = array(
        'library_id' => $result["library_id"],
        'icon_id' => $result["icon_id"],
        'code' => "form",
        'name' => $name,
        'model' => "Form_Model_Form",
        'desktop_uri' => "form/application/",
        'mobile_uri' => "form/mobile_view/",
        'only_once' => 0,
        'is_ajax' => 1,
        'position' => 190,
        'backoffice_description' => 'This feature is disabled by default since update 4.18.5 and is deprecated in favor of Form v2.'
    );

    $option = Siberian_Feature::install($category, $data, array('code'));

    # Icons Flat
    $icons = array(
        '/form/form1-flat.png',
        '/form/form2-flat.png',
        '/form/form3-flat.png',
    );

    Siberian_Feature::installIcons("{$name}-flat", $icons);

    try {
        $this->query("ALTER TABLE `form` CHANGE `email` `email` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;");
    } catch (Exception $e) {
        if (method_exists($this, "log")) {
            $this->log("E-mail already TEXT, skipping.");
        }
    }

    // Disable only not done yet!
    // deprecated from version 4.18.5
    if (__get('form_v1_deprecated') !== 'done') {
        $option
            ->setIsEnabled(0)
            ->save();

        __set('form_v1_deprecated', 'done');
    }

} else {
    // Do not install anymore Form v1 feature (but still db scheme), if new install!
}
