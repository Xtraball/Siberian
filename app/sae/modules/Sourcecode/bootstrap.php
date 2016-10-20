<?php
class Sourcecode_Bootstrap {

    public static function init($bootstrap) {
        # Exporter
        Siberian_Exporter::register("source_code", "Sourcecode_Model_Sourcecode");

    }
}
