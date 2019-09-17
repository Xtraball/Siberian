<?php

# Payment gateways ACL group
$acls = [
    [
        "code" => "payment_gateways_manager",
        "label" => "Payment gateways",
        "url" => null,
    ],
];

if ($stripe->getId()) {
    foreach ($acls as $acl) {
        $acl["parent_id"] = $stripe->getId();

        $resource = new Acl_Model_Resource();
        $resource
            ->setData($acl)
            ->insertOrUpdate(["code"]);

        if (!empty($acl["children"])) {
            foreach ($acl["children"] as $childResource) {
                $childResource["parent_id"] = $resource->getId();

                $child = new Acl_Model_Resource();
                $child
                    ->setData($childResource)
                    ->insertOrUpdate(["code"]);
            }
        }
    }
}



