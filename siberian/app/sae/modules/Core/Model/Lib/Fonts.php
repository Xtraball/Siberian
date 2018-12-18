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
    public static function getFonts($withGroups = false)
    {
        $fonts = [
            "Serif" => [
                "Merriweather" => [
                    "cyrillic" => true,
                ],
                "Playfair+Display" => [
                    "cyrillic" => true,
                ],
                "Bitter" => [
                    "cyrillic" => false,
                ],
                "Cinzel" => [
                    "cyrillic" => false,
                ],
                "Cardo" => [
                    "cyrillic" => true,
                ],
                "Mate" => [
                    "cyrillic" => false,
                ],
            ],
            "Sans Serif" => [
                "Montserrat" => [
                    "cyrillic" => true,
                ],
                "Text+Me+One" => [
                    "cyrillic" => false,
                ],
                "Dosis" => [
                    "cyrillic" => false,
                ],
                "Fjalla+One" => [
                    "cyrillic" => false,
                ],
                "Play" => [
                    "cyrillic" => true,
                ],
                "Nunito" => [
                    "cyrillic" => false,
                ],
            ],
            "Display" => [
                "Ranga" => [
                    "cyrillic" => false,
                ],
                "Comfortaa" => [
                    "cyrillic" => true,
                ],
                "Righteous" => [
                    "cyrillic" => false,
                ],
                "Code" => [
                    "cyrillic" => false,
                ],
                "Overlock" => [
                    "cyrillic" => false,
                ],
                "Forum" => [
                    "cyrillic" => true,
                ],
            ],
            "Handwriting" => [
                "Patrick+Hand" => [
                    "cyrillic" => false,
                ],
                "Tangerine" => [
                    "cyrillic" => false,
                ],
                "Mali" => [
                    "cyrillic" => false,
                ],
                "Itim" => [
                    "cyrillic" => false,
                ],
                "Short+Stack" => [
                    "cyrillic" => false,
                ],
                "Dancing+Script" => [
                    "cyrillic" => false,
                ],
            ],
            "Monospace" => [
                "Roboto+Mono" => [
                    "cyrillic" => true,
                ],
                "Inconsolata" => [
                    "cyrillic" => false,
                ],
                "Cousine" => [
                    "cyrillic" => true,
                ],
                "Oxygen+Mono" => [
                    "cyrillic" => false,
                ],
                "Fira+Mono" => [
                    "cyrillic" => true,
                ],
                "Nova+Mono" => [
                    "cyrillic" => true,
                ],
            ],
        ];

        if (!$withGroups) {
            $allFonts = [];
            foreach ($fonts as $group => $families) {
                foreach ($families as $fontFamily => $options) {
                    $allFonts = array_merge($allFonts, [$fontFamily]);
                }
            }
            return $allFonts;
        }

        return $fonts;
    }

}