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
                "Merriweather",
                "Playfair+Display",
                "Bitter",
                "Cinzel",
                "Cardo",
                "Mate",
            ],
            "Sans Serif" => [
                "Montserrat",
                "Text+Me+One",
                "Dosis",
                "Fjalla+One",
                "Play",
                "Nunito",
            ],
            "Display" => [
                "Ranga",
                "Comfortaa",
                "Righteous",
                "Code",
                "Overlock",
                "Forum",
            ],
            "Handwriting" => [
                "Patrick+Hand",
                "Tangerine",
                "Mali",
                "Itim",
                "Short+Stack",
                "Dancing+Script",
            ],
            "Monospace" => [
                "Roboto+Mono",
                "Inconsolata",
                "Cousine",
                "Oxygen+Mono",
                "Fira+Mono",
                "Nova+Mono",
            ],
        ];

        if (!$withGroups) {
            $allFonts = [];
            foreach ($fonts as $group => $values) {
                $allFonts = array_merge($allFonts, $values);
            }
            return $allFonts;
        }

        return $fonts;
    }

}