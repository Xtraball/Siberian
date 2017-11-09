<?php

$configs = array(
    array(
        "code" => "facebook_import_active",
        "label" => "Facebook import status",
        "value" => "1"
    )
);

foreach($configs as $data) {
    $config = new System_Model_Config();
    $config
        ->setData($data)
        ->insertOnce(array("code"));

}
