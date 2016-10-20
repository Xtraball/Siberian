<?php
class Topic_Bootstrap {

    public static function init($bootstrap) {
        # Exporter
        Siberian_Exporter::register("topic", "Topic_Model_Topic");

    }
}
