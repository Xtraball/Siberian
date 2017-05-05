<?php

class Cms_Form_Base extends Siberian_Form_Abstract {

    /**
     * @var string|null
     */
    public $feature_code = null;

    /**
     * @var string
     */
    public static $button_template = '
    <button type="button" class="btn color-blue add-cms-block" id="%ID%" data-blockid="%BLOCK_ID%">
        <i class="fa %BLOCK_ICON%"></i>
        <span>%TITLE%</span>
    </button>';

    /**
     * @var string
     */
    public static $input_loader = '<i class="input-loader fa fa-spinner fa-pulse fa-3x fa-fw" style="float: right;font-size: 20px;margin-top: -27px;margin-right: 2px;"></i>';

    /**
     * @param $title
     * @return Siberian_Form_Element_Html
     */
    public function addSections($title = "Add sections", $name = "sections_html") {

        $title = __($title);

        # Fetch available CMS Blocks
        $cms_application_block_model = new Cms_Model_Application_Block();
        $blocks = $cms_application_block_model->findAll();

        $html = '
    <p><b>'.__($title).'</b></p>';

        $current = array();
        foreach($blocks as $block) {
            if($this->feature_code == "places") {

                # Skip address block for Places.
                if($block->getType() == "address") {
                    continue;
                }
            }

            if(!in_array($block->getType(), $current)) {
                $tpl = self::$button_template;
                $tpl = str_replace(array(
                    "%ID%",
                    "%BLOCK_ID%",
                    "%BLOCK_ICON%",
                    "%TITLE%",
                ), array(
                    $block->getType(),
                    $block->getId(),
                    $block->getIcon(),
                    __($block->getTitle()),
                ), $tpl);

                $html .= $tpl;

                $current[] = $block->getType();
            }
        }

        $this->addSimpleHtml($name, $html, array("class" => "section-padding"));

        # Container for the blocks
        $this->addSimpleHtml($name."_container", "", array("class" => "blocks-container section-padding"));

        return $this;
    }

}