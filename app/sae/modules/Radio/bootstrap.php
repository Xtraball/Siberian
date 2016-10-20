<?php
class Radio_Bootstrap {

    public static function init($bootstrap) {
        # Exporter
        Siberian_Exporter::register("radio", "Radio_Model_Radio", array(
            "all" => __("All data"),
            "safe" => __("Clean-up sensible data"),
        ));

    }
}
