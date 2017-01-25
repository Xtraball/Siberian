<?php

$resource_settings = new Acl_Model_Resource();
$resource_settings = $resource_settings->find("editor_settings", "code");

if($resource_settings->getId()) {
    //Create new section API KEYS Acl
    $data = array(
        "code" => "editor_settings_apis",
        "label" => "Access the APIs Keys settings from the editor",
        "url" => "application/settings_apis/*",
        "parent_id" => $resource_settings->getId()
    );

    $api_resource = new Acl_Model_Resource();
    $api_resource->setData($data)->insertOrUpdate(array("code"));

    //Create Flickr ACL
    $data = array(
        "code" => "editor_settings_flickr",
        "label" => "Access the Flickr settings from the editor",
        "parent_id" => $api_resource->getId()
    );
    $flickr_resource = new Acl_Model_Resource();
    $flickr_resource->setData($data)->insertOrUpdate(array("code"));

    $old_to_change = array(
        "editor_settings_facebook",
        "editor_settings_instagram",
        "editor_settings_twitter"
    );

    foreach($old_to_change as $code) {
        $change_resource = new Acl_Model_Resource();
        $change_resource = $change_resource->find($code, "code");
        if($change_resource->getId()) {
            $change_resource
                ->setParentId($api_resource->getId())
                ->setUrl("")
                ->insertOrUpdate(array("code"));
        }
    }
}
