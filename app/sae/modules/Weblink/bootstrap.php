<?php
class Weblink_Bootstrap {

    public static function init($bootstrap) {
        # Exporter
        Siberian_Exporter::register("weblink_mono", "Weblink_Model_Weblink");
        Siberian_Exporter::register("weblink_multi", "Weblink_Model_Weblink");

    }
}
