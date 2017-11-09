<?php

class Cms_Form_Block_Image_Abstract extends Cms_Form_Block_Abstract {

    /**
     * @var string
     */
    public static $image_template = "";

    /**
     * @param $block
     * @return $this
     */
    public function loadBlock($block) {
        if($this->getElement("description")) {
            $this->getElement("description")->setValue($block->getDescription());
        }

        $images_html = array();
        $images = $block->getLibrary();
        if($images) {
            foreach($images as $image) {

                $tmp = static::$image_template;
                $tmp = str_replace(array(
                    "%UNIQID%",
                    "%IMAGE_PATH%",
                    "#THUMBNAIL_PATH#",
                ), array(
                    $this->getUniqid(),
                    $image->getData("image_url"),
                    "/images/application".$image->getData("image_url"),
                ), $tmp);

                $images_html[] = $tmp;
            }
        }

        $cms_images_container = '
<div class="cms-images-container">'.implode("", $images_html).'</div>';

        $this->addSimpleHtml("cms-images-container", $cms_images_container);

        return $this;
    }

}