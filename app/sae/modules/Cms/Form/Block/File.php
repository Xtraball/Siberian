<?php

class Cms_Form_Block_File extends Cms_Form_Block_Abstract {

    /**
     * @var string
     */
    public static $file_template = '
<div class="cms-file">
    #TEXT#
    <div class="cms-file-delete">
        <i class="fa fa-times"></i>
    </div>
    <input type="hidden" name="block[%UNIQID%][file][file]" value="%FILE_PATH%" />
</div>';

    public function init() {
        parent::init();

        $this
            ->setAction(__path("/cms/application_page_block_file/upload"))
            ->setAttrib("id", "form-cms-block-file-".$this->uniqid)
        ;

        $file_uploader = $this->addSimpleFile("file_uploader", __("Add attachement"));
        $file_uploader->setBelongsTo("block[".$this->uniqid."][file]");

        $cms_file_container = '
<div class="cms-file-container section-padding"></div>';

        $file_container = $this->addSimpleHtml("cms-file-container", $cms_file_container);

        $value_id = $this->addSimpleHidden("value_id");
        $value_id
            ->setRequired(true)
        ;
    }

}