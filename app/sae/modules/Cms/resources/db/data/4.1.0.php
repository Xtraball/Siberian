<?php
$data = array(
    'type' => 'slider',
    'position' => 7,
    'icon' => 'icon-play-circle',
    'title' => 'Slider',
    'template' => 'cms/application/page/edit/block/slider.phtml',
    'mobile_template' => 'cms/page/%s/view/block/slider.phtml'
);

$block = new Cms_Model_Application_Block();
$block = $block->find("slider", "type");
$block->setData($data)->save();
