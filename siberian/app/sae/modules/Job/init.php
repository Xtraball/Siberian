<?php

use Siberian\Assets;
use Siberian\Exporter;
use Siberian\Translation;

/**
 * @param $bootstrap
 */
$init = function ($bootstrap) {
    // Register places scss for dynamic rebuild in colors page!
    Assets::registerScss([
        "/app/sae/modules/Job/features/job/scss/job.scss"
    ]);

    // Exporter!
    Exporter::register("job", "Job_Model_Job");

    $translationFile = path("/app/sae/modules/Job/resources/translations/default/job.po");
    Translation::registerExtractor("job", "Job", $translationFile);
};
