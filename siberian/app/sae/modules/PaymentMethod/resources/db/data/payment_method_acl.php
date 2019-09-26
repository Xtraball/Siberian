<?php

# Payment gateways ACL group
$acls = [
    [
        "code" => "payment_gateways_manager",
        "label" => "Payment gateways",
        "url" => null,
    ],
];

foreach ($acls as $acl) {
    $resource = new Acl_Model_Resource();
    $resource
        ->setData($acl)
        ->insertOrUpdate(["code"]);
}



