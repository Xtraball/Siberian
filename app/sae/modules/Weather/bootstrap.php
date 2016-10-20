<?php
class Weather_Bootstrap {

    public static function init($bootstrap) {
        # Exporter
        Siberian_Exporter::register("weather", "Weather_Model_Weather");

    }
}
