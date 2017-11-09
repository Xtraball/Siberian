<?php

class Cms_Form_Block_Cover extends Cms_Form_Block_Image_Abstract {

    /**
     * @var string
     */
    public $blockType = 'cover';

    /**
     * @var string
     */
    public static $image_template = '
<div class="cms-image" style="background-image: url(#THUMBNAIL_PATH#);">
    <div class="cms-image-delete">
        <i class="fa fa-times"></i>
    </div>
    <img src="/images/application/placeholder/blank-512.png" class="cms-image-unit" />
    <input type="hidden" name="block[%UNIQID%][cover][images][]" value="%IMAGE_PATH%" />
</div>';

    public function init() {
        parent::init();

        $this
            ->setAction(__path("/template/crop/upload"))
            ->setAttrib("id", "form-cms-block-cover-".$this->uniqid)
        ;

        $this->removeElement("description");
        $this->removeElement("image_uploader");

        $cover_uploader = $this->addSimpleFile("image_uploader", __("Add cover"));
        $cover_uploader->setBelongsTo("block[".$this->uniqid."][cover]");

        $container = $this->removeElement("cmsimagescontainer");

        $cms_images_container = '
<div class="cms-images-container col-md-7 col-md-offset-3"></div>';

        $pictures_container = $this->addSimpleHtml("cms-images-container", $cms_images_container);

    }

}