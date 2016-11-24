<?php

class Core_Model_Lib_String extends Core_Model_Default {

    public static function camelize($str) {
        $str = trim(preg_replace('/[[:upper:]|(\d)]/',' \0', $str));
        return strtolower(strtr($str, ' ', '_'));
    }

    public static function decamelize($str) {
        return preg_replace('/(^|_)([a-z])/e', 'strtoupper("\\2")', $str);
    }


    public static function formatShortName($name) {
        $shortname = trim($name);

        if(mb_strlen($shortname, 'utf8') > 9) {
            $shortname = trim(mb_substr($name, 0 , 4, 'utf8')).'...';
            $shortname.= trim(mb_substr($name, strlen($name)-4 , strlen($name), 'utf8'));
        }

        return $shortname;
    }

    public static function format($str, $tolower = false) {
        $str = htmlentities($str, ENT_NOQUOTES, 'utf-8');
        $str = preg_replace('/&amp;/', 'AND', $str);
        $str = preg_replace('/&([A-za-z])(?:acute|cedil|circ|grave|orn|ring|slash|th|tilde|uml|&amp;);/', '\1', $str);
        $str = preg_replace('/&([A-za-z]{2})(?:lig);/', '\1', $str); // pour les ligatures e.g. '&oelig;'
        $str = preg_replace('/\W/', '', $str);
        if($tolower) {
            $str = strtolower($str);
        }

        return $str;
    }

    public static function truncate($str, $limit, $replacement = '...') {
        if (mb_strlen($str, 'utf8') < $limit) return $str;

        $str = (mb_substr($str, 0, $limit, 'utf8'));
//        $str = strrev(mb_substr($str, strpos($str, ' ')));
        return trim($str) . $replacement;
    }

    public function stripAccents($str) {
        $str = htmlentities($str, ENT_NOQUOTES, 'utf-8');

        $str = preg_replace('#&([A-za-z])(?:acute|cedil|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
        $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str); // pour les ligatures e.g. '&oelig;'
        $str = preg_replace('#&[^;]+;#', '', $str); // supprime les autres caractères

        return $str;
    }

    public static function generate($length = 6) {

        $characts    = 'abcdefghijklmnopqrstuvwxyz';
        $characts   .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $characts   .= '1234567890';
        $random_code = '';

        for($i=0;$i < $length;$i++) {
            $random_code .= substr($characts,rand()%(strlen($characts)),1);
        }

        return $random_code;
    }

    public static function formatBundleId($parts_bundle_id) {

        foreach($parts_bundle_id as $k => $part) {
            $part = self::format($part);
            if(is_numeric(substr($part, 0, 1))) {
                $part = "a".$part;
            }
            if($part === "new") {
                $part = "new_";
            }
            $parts_bundle_id[$k] = $part;
        }

        return implode(".", $parts_bundle_id);
    }

    public static function formatLanguageCodeForAndroid($language_code) {

        if(stripos($language_code, "_") !== false) {
            $language_code = explode("_", $language_code);
            if(count($language_code) == 2) {
                $language_code[1] = "r".$language_code[1];
            }
            $language_code = implode("-", $language_code);
        }

        return $language_code;

    }

}