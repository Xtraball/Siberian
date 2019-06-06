<?php

use Siberian\Feature;
use Siberian\Assets;

# Job module, data.
$name = "Job";
$category = "social";

# Icons
$icons = [
    "/app/sae/modules/Job/resources/media/library/job1.png",
    "/app/sae/modules/Job/resources/media/library/job2.png",
];

$result = Feature::installIcons($name, $icons);

# Install the Feature
$data = [
    'library_id' => $result["library_id"],
    'icon_id' => $result["icon_id"],
    "code" => "job",
    "name" => $name,
    "model" => "Job_Model_Job",
    "desktop_uri" => "job/application/",
    "mobile_uri" => "job/mobile_list/",
    "mobile_view_uri" => "job/mobile_view/",
    "mobile_view_uri_parameter" => "job_id",
    "only_once" => 0,
    "is_ajax" => 1,
    "use_my_account" => 1,
    "position" => 1000,
    "social_sharing_is_available" => 1
];

$option = Feature::install($category, $data, ["code"]);
Feature::installAcl($option);

# Layouts
$layout_data = [1];
$slug = "job";

Feature::installLayouts($option->getId(), $slug, $layout_data);

# Icons Flat
$icons = [
    "/app/sae/modules/Job/resources/media/library/job1-flat.png",
    "/app/sae/modules/Job/resources/media/library/job2-flat.png",
];

Feature::installIcons("{$name}-flat", $icons);

# Copy assets at install time
Assets::copyAssets("/app/sae/modules/Job/resources/var/apps/");

