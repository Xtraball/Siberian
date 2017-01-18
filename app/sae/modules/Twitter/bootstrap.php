<?php
class Twitter_Bootstrap {

    public static function init($bootstrap) {
        # Exporter
        Siberian_Exporter::register("twitter", "Twitter_Model_Twitter");

    }
}
