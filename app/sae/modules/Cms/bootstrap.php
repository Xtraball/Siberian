<?php
class Cms_Bootstrap {

    public static function init($bootstrap) {
        Siberian_Cache_Design::overrideCoreDesign("Cms");
    }
}
