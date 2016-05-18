<?php

class Siberian_Pdf extends Zend_Pdf {

    /**
     * @param mixed $param1
     * @param mixed $param2
     * @return Siberian_Pdf_Page
     */
    public function newPage($param1, $param2 = null) {

        require_once 'Siberian/Pdf/Page.php';

        if ($param2 === null) {
            return new Siberian_Pdf_Page($param1, $this->_objFactory);
        } else {
            return new Siberian_Pdf_Page($param1, $param2, $this->_objFactory);
        }
    }

    public function getTextWidth($text, $font, $font_size) {

        $drawing_text = iconv('', 'UTF-8', $text);
        $characters    = array();
        for ($i = 0; $i < strlen($drawing_text); $i++) {
            $characters[] = (ord($drawing_text[$i++]) << 8) | ord ($drawing_text[$i]);
        }
        $glyphs        = $font->glyphNumbersForCharacters($characters);
        $widths        = $font->widthsForGlyphs($glyphs);
        $text_width   = (array_sum($widths) / $font->getUnitsPerEm()) * $font_size;

        return $text_width;

    }

}
