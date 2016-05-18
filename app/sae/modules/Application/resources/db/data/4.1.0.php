<?php

/** Smoothing databases from 4.1.0 */
$layouts = array(
    array('name' => 'Layout 1',              'visibility' => Application_Model_Layout_Homepage::VISIBILITY_ALWAYS,   'code' => 'layout_1',   'preview' => '/customization/layout/homepage/layout_1.png',   'use_more_button' => 1, 'use_horizontal_scroll' => 0, 'number_of_displayed_icons' => 5,     'position' => "bottom", "order" => 10, "is_active" => 1),
    array('name' => 'Layout 2',              'visibility' => Application_Model_Layout_Homepage::VISIBILITY_HOMEPAGE, 'code' => 'layout_2',   'preview' => '/customization/layout/homepage/layout_2.png',   'use_more_button' => 1, 'use_horizontal_scroll' => 0, 'number_of_displayed_icons' => 10,    'position' => "bottom", "order" => 20, "is_active" => 1),
    array('name' => 'Layout 3',              'visibility' => Application_Model_Layout_Homepage::VISIBILITY_HOMEPAGE, 'code' => 'layout_3',   'preview' => '/customization/layout/homepage/layout_3.png',   'use_more_button' => 0, 'use_horizontal_scroll' => 0, 'number_of_displayed_icons' => null,  'position' => "bottom", "order" => 30, "is_active" => 1),
    array('name' => 'Layout 4',              'visibility' => Application_Model_Layout_Homepage::VISIBILITY_HOMEPAGE, 'code' => 'layout_4',   'preview' => '/customization/layout/homepage/layout_4.png',   'use_more_button' => 0, 'use_horizontal_scroll' => 0, 'number_of_displayed_icons' => null,  'position' => "bottom", "order" => 40, "is_active" => 1),
    array('name' => 'Layout 5',              'visibility' => Application_Model_Layout_Homepage::VISIBILITY_HOMEPAGE, 'code' => 'layout_5',   'preview' => '/customization/layout/homepage/layout_5.png',   'use_more_button' => 0, 'use_horizontal_scroll' => 0, 'number_of_displayed_icons' => null,  'position' => "bottom", "order" => 50, "is_active" => 1),
    array('name' => 'Layout 6',              'visibility' => Application_Model_Layout_Homepage::VISIBILITY_HOMEPAGE, 'code' => 'layout_6',   'preview' => '/customization/layout/homepage/layout_6.png',   'use_more_button' => 0, 'use_horizontal_scroll' => 0, 'number_of_displayed_icons' => null,  'position' => "bottom", "order" => 60, "is_active" => 1),
    array('name' => 'Layout 7',              'visibility' => Application_Model_Layout_Homepage::VISIBILITY_HOMEPAGE, 'code' => 'layout_7',   'preview' => '/customization/layout/homepage/layout_7.png',   'use_more_button' => 0, 'use_horizontal_scroll' => 0, 'number_of_displayed_icons' => null,  'position' => "bottom", "order" => 70, "is_active" => 1),
    array('name' => 'Layout 8',              'visibility' => Application_Model_Layout_Homepage::VISIBILITY_HOMEPAGE, 'code' => 'layout_8',   'preview' => '/customization/layout/homepage/layout_8.png',   'use_more_button' => 0, 'use_horizontal_scroll' => 0, 'number_of_displayed_icons' => null,  'position' => "bottom", "order" => 80, "is_active" => 0),
    array('name' => 'Layout 9',              'visibility' => Application_Model_Layout_Homepage::VISIBILITY_TOGGLE,   'code' => 'layout_9',   'preview' => '/customization/layout/homepage/layout_9.png',   'use_more_button' => 0, 'use_horizontal_scroll' => 0, 'number_of_displayed_icons' => null,  'position' => "left",   "order" => 90, "is_active" => 1),
    array('name' => 'Layout 10',             'visibility' => Application_Model_Layout_Homepage::VISIBILITY_HOMEPAGE, 'code' => 'layout_10',  'preview' => '/customization/layout/homepage/layout_10.png',  'use_more_button' => 1, 'use_horizontal_scroll' => 0, 'number_of_displayed_icons' => 5,     'position' => 'bottom', "order" => 100, "is_active" => 1),
    array('name' => 'Layout 3 - Horizontal', 'visibility' => Application_Model_Layout_Homepage::VISIBILITY_HOMEPAGE, 'code' => 'layout_3_h', 'preview' => '/customization/layout/homepage/layout_3-h.png', 'use_more_button' => 0, 'use_horizontal_scroll' => 1, "number_of_displayed_icons" => 6,     'position' => "bottom", "order" => 35, "is_active" => 1),
    array('name' => 'Layout 4 - Horizontal', 'visibility' => Application_Model_Layout_Homepage::VISIBILITY_HOMEPAGE, 'code' => 'layout_4_h', 'preview' => '/customization/layout/homepage/layout_4-h.png', 'use_more_button' => 0, 'use_horizontal_scroll' => 1, "number_of_displayed_icons" => 6,     'position' => "bottom", "order" => 45, "is_active" => 1),
    array('name' => 'Layout 5 - Horizontal', 'visibility' => Application_Model_Layout_Homepage::VISIBILITY_HOMEPAGE, 'code' => 'layout_5_h', 'preview' => '/customization/layout/homepage/layout_5-h.png', 'use_more_button' => 0, 'use_horizontal_scroll' => 1, "number_of_displayed_icons" => 4,     'position' => "bottom", "order" => 55, "is_active" => 1)
);

foreach($layouts as $id => $data) {

    $id++;

    $layout = new Application_Model_Layout_Homepage();
    $layout->find($id);
    $layout->setData($data)
        ->save()
    ;

}


# Double check updates.siberiancms.com
$writer = new Zend_Config_Writer_Ini();
if(is_writable(APPLICATION_PATH . '/configs/app.ini')) {

    # Saving up current app.ini.
    if(!copy(APPLICATION_PATH . '/configs/app.ini', APPLICATION_PATH . '/../var/tmp/app.ini.bck')) {
        throw new Exception('Unable to backup you local app.ini, aborting...');
    }

    $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/app.ini', null, array('skipExtends' => true, 'allowModifications' => true));

    /** Configuring updates url. */
    $config->production->siberian = array(
        "updates" => array(
            "url" => "http://updates.siberiancms.com"
        )
    );
    $config->development->siberian = array(
        "updates" => array(
            "url" => "http://beta-updates.siberiancms.com"
        )
    );

    $writer->setConfig($config)
        ->setFilename(APPLICATION_PATH . '/configs/app.ini')
        ->write();
}
