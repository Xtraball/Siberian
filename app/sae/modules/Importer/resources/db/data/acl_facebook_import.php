<?php

$child_resource = array(
    "code"  => "editor_facebook_import",
    "label" => "Import features from Facebook",
    "url"   => "importer/*"
);

$tmp_res = new Acl_Model_Resource();
$tmp_res->find("editor", "code");
$tmp_res_id = $tmp_res->getId();

if(empty($tmp_res_id)) {
    throw new ErrorException("Cannot find Acl Resource with code: ".$parent_id." or feature_".$parent_id);
}

$child_resource["parent_id"] = $tmp_res->getId();

$child = new Acl_Model_Resource();
$child->setData($child_resource)
->insertOrUpdate(array("code"));