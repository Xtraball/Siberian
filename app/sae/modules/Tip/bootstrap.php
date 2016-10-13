<?php
class Tip_Bootstrap {

    public static function init($bootstrap) {
        # Exporter
        Siberian_Exporter::register("tip", "Tip_Model_Tip");

    }
}
