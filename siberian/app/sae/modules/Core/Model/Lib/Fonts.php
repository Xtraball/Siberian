<?php

/**
 * Class Core_Model_Lib_Fonts
 */
class Core_Model_Lib_Fonts
{

    /**
     * @var array
     */
    protected static $_fonts = [];

    /**
     * @return array
     */
    public static function getFonts()
    {
        $fonts = [
            "Abel",
            "Archivo+Narrow",
            "Bitter",
            "Cabin",
            "Exo",
            "Exo+2",
            "Hind",
            "Josefin+Sans",
            "Karla",
            "LatoLibre+Franklin",
            "Merriweather",
            "Montserrat",
            "Mukta",
            "Muli",
            "Noto+Sans+SC",
            "Noto+Serif",
            "Nunito",
            "Nunito+Sans",
            "Open+Sans",
            "Open+Sans+Condensed:300",
            "Oswald",
            "Oxygen",
            "PT+Sans",
            "Playfair+Display",
            "Poppins",
            "Raleway",
            "Roboto",
            "Source+Code+Pro",
            "Text+Me+One",
            "Ubuntu",
        ];

        return $fonts;
    }

}