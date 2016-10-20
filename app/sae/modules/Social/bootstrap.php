<?php
class Social_Bootstrap {

    public static function init($bootstrap) {
        # Exporter
        Siberian_Exporter::register("facebook", "Social_Model_Facebook");

    }
}
