<?php

$configs = array(
    array(
        "code" => "bootstraptour_active",
        "label" => "Tour is active?",
        "value" => "0"
    )
);

foreach($configs as $data) {
    $config = new System_Model_Config();
    $config
        ->setData($data)
        ->insertOnce(array("code"));

}