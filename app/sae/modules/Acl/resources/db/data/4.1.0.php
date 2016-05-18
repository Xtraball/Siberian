<?php
/* ACL */
$editor_settings_resource = new Acl_Model_Resource();
$editor_settings_resource->find("editor_settings", "code");

if($editor_settings_resource->getId()) {

    $data = array(
        "parent_id" => $editor_settings_resource->getId(),
        "code" => "editor_settings_instagram",
        "label" => "Access the Instagram tab",
        "url" => "application/settings_instagram/*"
    );

    $resource = new Acl_Model_Resource();
    $resource = $resource->find("editor_settings_instagram", "code");
    $resource->setData($data)
        ->save()
    ;

}