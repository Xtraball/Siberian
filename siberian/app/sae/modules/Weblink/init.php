<?php
$init = function($bootstrap) {
    # Exporter
    Siberian_Exporter::register("weblink_mono", "Weblink_Model_Type_Mono");
    Siberian_Exporter::register("weblink_multi", "Weblink_Model_Type_Multi");

};
