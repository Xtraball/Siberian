<?php
# All layouts assets
Siberian_Assets::copyAssets("/app/sae/modules/Layouts/resources/var/apps/");

# Updating apps without layoutOptions
$application_model = new Application_Model_Application();
$apps = $application_model->findAll();
foreach($apps as $app) {
    $layout_options = $app->getLayoutOptions();

    if(empty($layout_options)) {
        $layout_model = new Application_Model_Layout_Homepage();
        $layout = $layout_model->find($app->getLayoutId());
        $options = $layout->getOptions();
        $app->setLayoutOptions($options)->save();
    }
}