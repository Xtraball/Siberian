<?php

/**
 * This class overcomes a few common annoyances with the DOMDocument class,
 * such as saving partial HTML without automatically adding extra tags
 * and properly recognizing various encodings, specifically UTF-8.
 *
 * @author Artem Russakovskii
 * @version 0.4
 * @link http://beerpla.net
 * @link http://www.php.net/manual/en/class.domdocument.php
 */
class Dom_SmartDOMDocument extends DOMDocument {

    /**
     * Adds an ability to use the SmartDOMDocument object as a string in a string context.
     * For example, echo "Here is the HTML: $dom";
     */
    public function __toString() {
        return $this->saveHTMLExact();
    }

    /**
     * Load HTML with a proper encoding fix/hack.
     * Borrowed from the link below.
     *
     * @link http://www.php.net/manual/en/domdocument.loadhtml.php
     *
     * @param string $html
     * @param string $encoding
     */
    public function loadHTML($html, $encoding = "UTF-8") {
        $html = mb_convert_encoding($html, 'HTML-ENTITIES', $encoding);
        @parent::loadHTML($html); // suppress warnings
    }

    /**
     * Return HTML while stripping the annoying auto-added <html>, <body>, and doctype.
     *
     * @link http://php.net/manual/en/migration52.methods.php
     *
     * @return string
     */
    public function saveHTMLExact() {
        $content = preg_replace(array("/^\<\!DOCTYPE.*?<html><body>/si",
            "!</body></html>$!si"), "", $this->saveHTML());

        return $content;
    }

    /**
     * This test functions shows an example of SmartDOMDocument in action.
     * A sample HTML fragment is loaded.
     * Then, the first image in the document is cut out and saved separately.
     * It also shows that Russian characters are parsed correctly.
     *
     */
    public static function testHTML() {
        $content = <<<CONTENT
<div class='class1'>
  <img src='http://www.google.com/favicon.ico' />
  Some Text
  <p>Ñ€ÑƒÑÑÐºÐ¸Ð¹</p>
</div>
CONTENT;

        print "Before removing the image, the content is: " . htmlspecialchars($content) . "<br>";

        $content_doc = new Dom_SmartDOMDocument();
        $content_doc->loadHTML($content);

        try {
            $first_image = $content_doc->getElementsByTagName("img")->item(0);

            if ($first_image) {
                $first_image->parentNode->removeChild($first_image);

                $content = $content_doc->saveHTMLExact();

                $image_doc = new Dom_SmartDOMDocument();
                $image_doc->appendChild($image_doc->importNode($first_image, true));
                $image = $image_doc->saveHTMLExact();
            }
        } catch (Exception $e) {

        }

        print "After removing the image, the content is: " . htmlspecialchars($content) . "<br>";
        print "The image is: " . htmlspecialchars($image);
    }

}