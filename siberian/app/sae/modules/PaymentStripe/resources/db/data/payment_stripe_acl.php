<?php

# Various ACL
$acls = [
    [
        "code" => "payment_stripe_settings",
        "label" => "Stripe application settings",
        "url" => "paymentstripe/settings/index",
    ],
];

// Find feature_cabride
$paymentGateways = (new Acl_Model_Resource())->find("payment_gateways_manager", "code");

if ($paymentGateways->getId()) {
    foreach ($acls as $acl) {
        $acl["parent_id"] = $paymentGateways->getId();

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



