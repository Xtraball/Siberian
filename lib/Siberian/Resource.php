<?php
/**
 *
 */
class Siberian_Resource {

    /** Get External resource via http */
    public static function getExternal($resource) {
        list($status) = get_headers($resource);
        Zend_Debug::dump($status);
        if (strpos($status, '404') !== FALSE) {
            // URL is 404ing
        }
    }

    /** Get Local siberian file */
    public static function getLocal($resource) {

    }
}