<?php

/**
 * Class Siberian_Yaml
 *
 * Id: 
 */
ini_set('pcre.backtrack_limit', 1000000000);

class Siberian_Yaml {

    /**
     * @param $data
     * @return string
     * @throws Zend_Exception
     */
    public static function encode($data) {
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
        $parser_yaml = new Symfony\Component\Yaml\Yaml();
        $data = $parser_yaml::parse($content);

        return $data;
    }
}
