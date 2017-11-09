<?php

class Core_Model_Lib_Fonts
{

    protected static $_fonts = array();

    public static function getFonts() {
        $fonts = array(
            'Arial',
            'Helvetica',
            'Verdana',
            'Georgia',
            'Courier',
            'Times new roman',
            'Palatino'
        );

        sort($fonts);
        return $fonts;
    }

}