<?php

/**
 * Class Siberian_Pdf_Page
 */
class Siberian_Pdf_Page extends Zend_Pdf_Page
{
    /**
     * @param string $text
     * @param float $x
     * @param float $y
     * @param string $charEncoding
     * @return $this|Zend_Pdf_Canvas_Interface
     * @throws Zend_Pdf_Exception
     */
    public function drawText($text, $x, $y, $charEncoding = 'UTF-8')
    {
        parent::drawText($text, $x, $y, $charEncoding);
        return $this;
    }
}

