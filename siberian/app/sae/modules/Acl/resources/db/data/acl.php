<?php
# First role, Admin
$data = [
    "code" => "Admin",
    "label" => "Administrator : full access",
];

$acl_role = new Acl_Model_Role();
$acl_role
    ->setData($data)
    ->insertOnce(["code"]);

# Various ACL
$resource_data = [
    [
        "code" => "application",
        "label" => "Manage applications",
        "children" => [
            [
                "code" => "application_create",
                "label" => "Create an application",
                "url" => "admin/application/createpost",
            ],
            [
                "code" => "application_copy",
                "label" => "Copy an application (deprecated)",
                "url" => "admin/application/duplicate",
            ],
            [
                "code" => "application_delete",
                "label" => "Delete an application",
                "url" => "admin/application/delete",
            ]
        ]
    ],
    [
        "code" => "editor",
        "label" => "Access the application editor",
        "children" => [
            [
                "code" => "editor_design",
                "label" => "Access the Design tab",
                "url" => "application/customization_design_style/edit",
            ],
            [
                "code" => "editor_colors",
                "label" => "Access the Colors tab",
                "url" => "application/customization_design_colors/edit",
            ],
            [
                "code" => "editor_features",
                "label" => "Access the Features tab",
                "url" => "application/customization_features/list",
            ],
            [
                "code" => "editor_application",
                "label" => "Access the Application tab",
                "url" => "application/customization_publication_app/index",
            ],
            [
                "code" => "editor_publication",
                "label" => "Access the Publication tab",
                "url" => "application/customization_publication_infos/index",
            ],
            [
                "code" => "editor_facebook_import",
                "label" => "Import features from Facebook",
                "url" => "importer/*"
            ],
            [
                "code" => "editor_settings",
                "label" => "Access the settings from the editor",
                "children" => [
                    [
                        "code" => "editor_settings_tc",
                        "label" => "Access the Terms & Conditions tab",
                        "url" => "application/settings_tc/*",
                    ], [
                        "code" => "editor_settings_facebook",
                        "label" => "Access the Facebook tab",
                        "url" => "application/settings_facebook/*",
                    ], [
                        "code" => "editor_settings_domain",
                        "label" => "Access the Domain tab",
                        "url" => "application/settings_domain/*",
                    ], [
                        "code" => "editor_settings_messages",
                        "label" => "Access the editor messages",
                        "url" => "message/application/*"
                    ], [
                        "code" => "editor_settings_instagram",
                        "label" => "Access the Instagram tab",
                        "url" => "application/settings_instagram/*"
                    ], [
                        "code" => "editor_settings_twitter",
                        "label" => "Access the Twitter tab",
                        "url" => "application/settings_twitter/*"
                    ], [
                        "code" => "editor_settings_advanced",
                        "label" => "Access the Advanced tab",
                        "url" => "application/settings_advanced/*"
                    ]
                ]
            ]
        ]
    ],
    [
        "code" => "delete_feature",
        "label" => "Delete a feature"
    ],
    [
        "code" => "admin_access_management",
        "label" => "Manage the editor users",
        "url" => "admin/access_management/*"
    ],
    [
        "code" => "push_admin_global",
        "label" => "Send global push notifications",
        "url" => "push/admin/*"
    ],
    [
        "code" => "analytics_application",
        "label" => "Analytics application page",
        "url" => "application/analytics_application/*"
    ],
    [
        "code" => "analytics_feature",
        "label" => "Analytics feature page",
        "url" => "application/analytics_feature/*"
    ],
    [
        "code" => "promote",
        "label" => "Promote page",
        "url" => "application/promote/*"
    ],
    [
        "code" => "users",
        "label" => "Users page",
        "url" => "customer/application/list"
    ],
    [
        "code" => "support",
        "label" => "Support"
    ]
];

$option = new Application_Model_Option();
$options = $option->findAll();

$features_resources = [
    "code" => "feature",
    "label" => "Features",
    "children" => []
];

foreach ($options as $option) {
    $features_resources["children"][] = [
        "code" => "feature_" . $option->getCode(),
        "label" => $option->getname(),
        "url" => $option->getDesktopUri() . "*"
    ];
}

$resource_data[] = $features_resources;

foreach ($resource_data as $data) {
    $resource = new Acl_Model_Resource();
    $resource
        ->setData($data)
        ->insertOrUpdate(["code"]);

    if (!empty($data["children"])) {
        foreach ($data["children"] as $child_resource) {
            $child_resource["parent_id"] = $resource->getId();

            $child = new Acl_Model_Resource();
            $child
                ->setData($child_resource)
                ->insertOrUpdate(["code"]);

            if (!empty($child_resource["children"])) {

                foreach ($child_resource["children"] as $child_child_resource) {
                    $child_child_resource["parent_id"] = $child->getId();

                    $child_child = new Acl_Model_Resource();
                    $child_child
                        ->setData($child_child_resource)
                        ->insertOrUpdate(["code"]);
                }
            }

        }

    }

}

$resource = new Acl_Model_Resource();
$resource->find("analytics", "code")->delete();
