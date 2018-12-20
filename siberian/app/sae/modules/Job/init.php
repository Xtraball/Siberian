<?php

/**
 * @param $bootstrap
 */
$init = function($bootstrap) {
    // Register assets!
    \Siberian_Assets::registerAssets("Job", "/app/sae/modules/Job/resources/var/apps/");
    \Siberian_Assets::addJavascripts([
        "modules/job/controllers/job.js",
        "modules/job/factories/job.js",
    ]);

    \Siberian_Assets::addStylesheets([
        "modules/job/css/styles.css",
    ]);

    // Exporter!
    \Siberian_Exporter::register("job", "Job_Model_Job");
};
