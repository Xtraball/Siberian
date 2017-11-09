<?php
$default = Siberian_Json::encode(array(
    "application" => array(
        "create" => true,
        "update" => true,
        "grant_user" => true,
        "revoke_user" => true,
    ),
    "user" => array(
        "exist" => true,
        "authenticate" => true,
        "create" => true,
        "update" => true,
        "forgot_password" => true,
    ),
));

$api_model = new Api_Model_User();
$users = $api_model->findAll();
foreach($users as $user) {
    $acl = $user->getAcl();
    if(empty($acl)) {
        $user->setAcl($default)->save();
    }
}