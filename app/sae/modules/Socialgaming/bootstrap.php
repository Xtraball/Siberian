<?php
class Socialgaming_Bootstrap {

    public static function init($bootstrap) {
        # Exporter
        Siberian_Exporter::register("social_gaming", "Socialgaming_Model_Game");

    }
}
