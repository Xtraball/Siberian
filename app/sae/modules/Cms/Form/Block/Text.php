<?php

class Cms_Form_Block_Text extends Cms_Form_Block_Abstract {

    /**
     * @var string
     */
    public $blockType = 'text';

    /**
     * @var string
     */
    public static $image_template = '
<div class="cms-image" style="background-image: url(#THUMBNAIL_PATH#);">
    <div class="cms-image-delete">
        <i class="fa fa-times"></i>
    </div>
    <img src="/images/application/placeholder/blank-512.png" class="cms-image-unit" />
    <input type="hidden" name="block[%UNIQID%][text][image]" value="%IMAGE_PATH%" />
</div>';

    public function init() {
        parent::init();

        $this
            ->setAction(__path("/template/crop/upload"))
            ->setAttrib("id", "form-cms-block-text-".$this->uniqid)
        ;

        $text = $this->addSimpleTextarea("text", __("Text"), false, array("ckeditor" => "cms"));
        $text->setBelongsTo("block[".$this->uniqid."][text]");
        $text->setRichtext();

        # Image section
        $image = $this->addSimpleFile("image_upload", __("Add a picture"));
        $image->setBelongsTo("block[".$this->uniqid."][text]");
        $image->addClass("cms-text-image");

        $cms_text_image_container = '
<div class="cms-text-image cms-text-image-container col-md-7 col-md-offset-3"></div>';

        $image_container = $this->addSimpleHtml("cms-text-image-container", $cms_text_image_container);

        $html_alignment = '
<div class="cms-text-option">
    <span class="option-title col-md-3">'.__("Alignment").'</span>
    <div class="col-md-7">
        <a href="javascript:void(0);" class="btn color-blue cms-text-align selected" data-align="left">
            <i class="fa fa-align-left"></i>
        </a>
        <a href="javascript:void(0);" class="btn color-blue cms-text-align" data-align="right">
            <i class="fa fa-align-right"></i>
        </a>
    </div>
</div>';

        $image_alignment = $this->addSimpleHtml("image_alignment", $html_alignment);
        $image_alignment->addClass("cms-text-image");

        $html_size = '
<div class="cms-text-option">
    <div class="option-title col-md-3">'.__("Size").'</div>
    <div class="col-md-7">
        <a href="javascript:void(0);" class="btn color-blue cms-text-size selected" data-size="25">'.__("Small").'</a>
        <a href="javascript:void(0);" class="btn color-blue cms-text-size" data-size="35">'.__("Medium").'</a>
        <a href="javascript:void(0);" class="btn color-blue cms-text-size" data-size="45">'.__("Large").'</a>
    </div>
</div>';

        $image_size = $this->addSimpleHtml("image_size", $html_size);
        $image_size->addClass("cms-text-image");

        $alignment_input = $this->addSimpleHidden("alignment");
        $alignment_input->setBelongsTo("block[".$this->uniqid."][text]");
        $alignment_input->addClass("cms-text-alignment-input");
        $alignment_input->setValue("left");

        $size_input = $this->addSimpleHidden("size");
        $size_input->setBelongsTo("block[".$this->uniqid."][text]");
        $size_input->addClass("cms-text-size-input");
        $size_input->setValue(25);

        $value_id = $this->addSimpleHidden("value_id");
        $value_id
            ->setRequired(true)
        ;
    }

    /**
     * @param $block
     * @return $this
     */
    public function loadBlock($block) {
        $this->getElement("text")->setValue($block->getContent());
        $this->getElement("size")->setValue($block->getSize());
        $this->getElement("alignment")->setValue($block->getAlignment());
        $this->getElement("image_upload")->setValue($block->getImage());

        return $this;
    }

}