<?php
# Job module, data.
$name = "Job";
$category = "social";

# Icons
$icons = array(
    "/app/sae/modules/Job/resources/media/library/job1.png",
    "/app/sae/modules/Job/resources/media/library/job2.png",
);

$result = Siberian_Feature::installIcons($name, $icons);

# Install the Feature
$data = array(
    'library_id'                    => $result["library_id"],
    'icon_id'                       => $result["icon_id"],
    "code"                          => "job",
    "name"                          => $name,
    "model"                         => "Job_Model_Job",
    "desktop_uri"                   => "job/application/",
    "mobile_uri"                    => "job/mobile_list/",
    "mobile_view_uri"               => "job/mobile_view/",
    "mobile_view_uri_parameter"     => "job_id",
    "only_once"                     => 0,
    "is_ajax"                       => 1,
    "use_my_account"                => 1,
    "position"                      => 1000,
    "social_sharing_is_available"   => 1
);

$option = Siberian_Feature::install($category, $data, array("code"));
Siberian_Feature::installAcl($option);

# Layouts
$layout_data = array(1);
$slug = "job";

Siberian_Feature::installLayouts($option->getId(), $slug, $layout_data);

# Icons Flat
$icons = array(
    "/app/sae/modules/Job/resources/media/library/job1-flat.png",
    "/app/sae/modules/Job/resources/media/library/job2-flat.png",
);

Siberian_Feature::installIcons("{$name}-flat", $icons);

# Copy assets at install time
Siberian_Assets::copyAssets("/app/sae/modules/Job/resources/var/apps/");

try {
    $this->query("ALTER TABLE `job_place` CHANGE `keywords` `keywords` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL;");
} catch(Exception $e) {
    // already executed
}

try {
    $this->query("ALTER TABLE `job_place` CHANGE `contract_type` `contract_type` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL;");
} catch(Exception $e) {
    // already executed
}

try {
    $this->query("ALTER TABLE `job_place` CHANGE `income_from` `income_from` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL;");
} catch(Exception $e) {
    // already executed
}

try {
    $this->query("ALTER TABLE `job_place` CHANGE `income_to` `income_to` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL;");
} catch(Exception $e) {
    // already executed
}


