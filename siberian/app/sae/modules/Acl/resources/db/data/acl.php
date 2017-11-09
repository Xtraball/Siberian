<?php
# First role, Admin
$data = array(
    "code"  => "Admin",
    "label" => "Administrator : full access",
);

$acl_role = new Acl_Model_Role();
$acl_role
    ->setData($data)
    ->insertOnce(array("code"));

# Various ACL
$resource_data = array(
    array(
        "code" => "application",
        "label" => "Manage applications",
        "children" => array(
            array(
                "code" => "application_create",
                "label" => "Create an application",
                "url" => "admin/application/createpost",
            ),
            array(
                "code" => "application_delete",
                "label" => "Delete an application",
                "url" => "admin/application/delete",
            )
        )
    ),
    array(
        "code" => "editor",
        "label" => "Access the application editor",
        "children" => array(
            array(
                "code" => "editor_design",
                "label" => "Access the Design tab",
                "url" => "application/customization_design_style/edit",
            ),
            array(
                "code" => "editor_colors",
                "label" => "Access the Colors tab",
                "url" => "application/customization_design_colors/edit",
            ),
            array(
                "code" => "editor_features",
                "label" => "Access the Features tab",
                "url" => "application/customization_features/list",
            ),
            array(
                "code" => "editor_application",
                "label" => "Access the Application tab",
                "url" => "application/customization_publication_app/index",
            ),
            array(
                "code" => "editor_publication",
                "label" => "Access the Publication tab",
                "url" => "application/customization_publication_infos/index",
            ),
            array(
                "code"  => "editor_facebook_import",
                "label" => "Import features from Facebook",
                "url"   => "importer/*"
            ),
            array(
                "code" => "editor_settings",
                "label" => "Access the settings from the editor",
                "children" => array(
                    array(
                        "code" => "editor_settings_tc",
                        "label" => "Access the Terms & Conditions tab",
                        "url" => "application/settings_tc/*",
                    ), array(
                        "code" => "editor_settings_facebook",
                        "label" => "Access the Facebook tab",
                        "url" => "application/settings_facebook/*",
                    ), array(
                        "code" => "editor_settings_domain",
                        "label" => "Access the Domain tab",
                        "url" => "application/settings_domain/*",
                    ), array(
                        "code" => "editor_settings_messages",
                        "label" => "Access the editor messages",
                        "url" => "message/application/*"
                    ), array(
                        "code" => "editor_settings_instagram",
                        "label" => "Access the Instagram tab",
                        "url" => "application/settings_instagram/*"
                    ), array(
                        "code" => "editor_settings_twitter",
                        "label" => "Access the Twitter tab",
                        "url" => "application/settings_twitter/*"
                    ), array(
                        "code" => "editor_settings_advanced",
                        "label" => "Access the Advanced tab",
                        "url" => "application/settings_advanced/*"
                    )
                )
            )
        )
    ),
    array(
        "code" => "admin_access_management",
        "label" => "Manage the editor users",
        "url" => "admin/access_management/*"
    ),
    array(
        "code" => "push_admin_global",
        "label" => "Send global push notifications",
        "url" => "push/admin/*"
    ),
    array(
        "code" => "analytics_application",
        "label" => "Analytics application page",
        "url" => "application/analytics_application/*"
    ),
    array(
        "code" => "analytics_feature",
        "label" => "Analytics feature page",
        "url" => "application/analytics_feature/*"
    ),
    array(
        "code" => "promote",
        "label" => "Promote page",
        "url" => "application/promote/*"
    ),
    array(
        "code" => "users",
        "label" => "Users page",
        "url" => "customer/application/list"
    ),
    array(
        "code" => "support",
        "label" => "Support"
    )
);

$option = new Application_Model_Option();
$options = $option->findAll();

$features_resources = array(
    "code" => "feature",
    "label" => "Features",
    "children" => array()
);

foreach($options as $option) {
    $features_resources["children"][] = array(
        "code"      => "feature_".$option->getCode(),
        "label"     => $option->getname(),
        "url"       => $option->getDesktopUri()."*"
    );
}

$resource_data[] = $features_resources;

foreach($resource_data as $data) {
    $resource = new Acl_Model_Resource();
    $resource
        ->setData($data)
        ->insertOrUpdate(array("code"));

    /** @todo should implement recursive ACL */
    if(!empty($data["children"])) {

        foreach($data["children"] as $child_resource) {
            $child_resource["parent_id"] = $resource->getId();

            $child = new Acl_Model_Resource();
            $child
                ->setData($child_resource)
                ->insertOrUpdate(array("code"));

            if(!empty($child_resource["children"])) {

                foreach($child_resource["children"] as $child_child_resource) {
                    $child_child_resource["parent_id"] = $child->getId();

                    $child_child = new Acl_Model_Resource();
                    $child_child
                        ->setData($child_child_resource)
                        ->insertOrUpdate(array("code"));
                }
            }

        }

    }

}

$resource = new Acl_Model_Resource();
$resource->find("analytics", "code")->delete();
