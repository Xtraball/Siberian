<?php

/**
 * Class Siberian_Yaml
 *
 * Id: 
 */
class Siberian_Yaml {

    /**
     * @param $data
     * @return string
     * @throws Zend_Exception
     */
    public static function encode($data) {
        $logger = Zend_Registry::get("logger");

        $parser_yaml = new Symfony\Component\Yaml\Yaml();
        $yaml = $parser_yaml::dump($data);

        return $yaml;
    }

    /**
     * @param $content
     * @return mixed
     * @throws Zend_Exception
     */
    public static function decode($content) {
        $logger = Zend_Registry::get("logger");

        $parser_yaml = new Symfony\Component\Yaml\Yaml();
        $data = $parser_yaml::parse($content);

        return $data;
    }
}
