<?php

class Template_Model_Design extends Core_Model_Default {

    const PATH_IMAGE = '/images/templates';

    protected $_blocks;

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Template_Model_Db_Table_Design';
        return $this;
    }

    public static function getCssPath($application) {

        $path = Core_Model_Directory::getPathTo("var/cache/css");
        $base_path = Core_Model_Directory::getBasePathTo("var/cache/css");
        $file = $application->getId().".css";
        if(!is_file("{$base_path}/{$file}")) {
            self::generateCss($application);
        }

        return "{$path}/{$file}";

    }

    public static function generateCss($application, $javascript = false) {

        $variables = array();
        $blocks = $application->getBlocks();

        if(!$javascript) {
            foreach ($blocks as $block) {

                if ($block->getColorVariableName() AND $block->getColor()) {
                    $variables[$block->getColorVariableName()] = $block->getColor();
                }
                if ($block->getBackgroundColorVariableName() AND $block->getBackgroundColor()) {
                    $variables[$block->getBackgroundColorVariableName()] = $block->getBackgroundColor();
                }
                if ($block->getBorderColorVariableName() AND $block->getBorderColor()) {
                    $variables[$block->getBorderColorVariableName()] = $block->getBorderColor();
                }

                foreach ($block->getChildren() as $child) {
                    if ($child->getColorVariableName() AND $child->getColor()) {
                        $variables[$child->getColorVariableName()] = $child->getColor();
                    }
                    if ($child->getBackgroundColorVariableName() AND $child->getBackgroundColor()) {
                        $variables[$child->getBackgroundColorVariableName()] = $child->getBackgroundColor();
                    }
                    if ($child->getBorderColorVariableName() AND $child->getBorderColor()) {
                        $variables[$child->getBorderColorVariableName()] = $child->getBorderColor();
                    }
                }

            }
        } else {
            foreach ($blocks as $block) {

                $block_id = (strlen(dechex($block->getId()))==2) ? dechex($block->getId()) : "0".dechex($block->getId());


                if ($block->getColorVariableName() AND $block->getColor()) {
                    $block_pos = "01";
                    $hex = "#".$block_id."00".$block_pos;

                    $variables[$block->getColorVariableName()] = $hex;
                }
                if ($block->getBackgroundColorVariableName() AND $block->getBackgroundColor()) {
                    $block_pos = "02";
                    $hex = "#".$block_id."00".$block_pos;

                    $variables[$block->getBackgroundColorVariableName()] = $hex;
                }
                if ($block->getBorderColorVariableName() AND $block->getBorderColor()) {
                    $block_pos = "03";
                    $hex = "#".$block_id."00".$block_pos;

                    $variables[$block->getBorderColorVariableName()] = $hex;
                }

                foreach ($block->getChildren() as $child) {
                    $child_id = (strlen(dechex($child->getId()))==2) ? dechex($child->getId()) : "0".dechex($child->getId());

                    if ($child->getColorVariableName() AND $child->getColor()) {
                        $child_pos = "01";
                        $hex = "#".$block_id.$child_id.$child_pos;

                        $variables[$child->getColorVariableName()] = $hex;
                    }
                    if ($child->getBackgroundColorVariableName() AND $child->getBackgroundColor()) {
                        $child_pos = "02";
                        $hex = "#".$block_id.$child_id.$child_pos;

                        $variables[$child->getBackgroundColorVariableName()] = $hex;
                    }
                    if ($child->getBorderColorVariableName() AND $child->getBorderColor()) {
                        $child_pos = "03";
                        $hex = "#".$block_id.$child_id.$child_pos;

                        $variables[$child->getBorderColorVariableName()] = $hex;
                    }
                }

            }

        }


        $font_family = '"Helvetica Neue", "Roboto", "Segoe UI", sans-serif';
        if($application->getFontFamily()) {
            $font_family = '"'.$application->getFontFamily().'", '.$font_family;
        }
        $variables['$font-family'] = $font_family;

        $content = Array();
        foreach(array("ionic.siberian.variables.scss", "ionic.siberian.style.scss") as $file) {
            $f = fopen(Core_Model_Directory::getBasePathTo("var/apps/browser/scss/{$file}"), "r");
            if ($f) {
                while (($line = fgets($f)) !== false) {
                    preg_match("/([\$a-zA-Z0-9_-]*)/", $line, $matches);
                    if (!empty($matches[0]) AND !empty($variables[$matches[0]])) {
                        $line = "{$matches[0]}: {$variables[$matches[0]]} !default;";
                    }
                    $content[] = $line;
                }
            }
        }

        $scss = implode("\n", $content);

        $compiler = Siberian_Scss::getCompiler();
        $compiler->addImportPath(Core_Model_Directory::getBasePathTo("var/apps/browser/lib/ionic/scss"));
        $compiler->addImportPath(Core_Model_Directory::getBasePathTo("var/apps/browser/scss"));

        $css = $compiler->compile('
            @import "_variables.scss";
            @import "_mixins.scss";
            ' . $scss
        );

        if($javascript) {
            return $css;
        } else {
            $folder = Core_Model_Directory::getBasePathTo("var/cache/css");
            $file = $application->getId().".css";
            if(!is_dir($folder)) mkdir($folder, 0777, true);
            file_put_contents("{$folder}/{$file}", $css);
        }
    }

    public function findAllWithCategory() {
        $all_templates = $this->findAll();
        $template_a_category = $this->getTable()->findAllWithCategory();
        $final_templates = array();

        foreach($all_templates as $template) {

            $tmp_category_ids = array();
            foreach($template_a_category as $template_category) {
                if($template->getDesignId() == $template_category["design_id"])
                    $tmp_category_ids[] = $template_category["category_id"];
            }
            $template->setCategoryIds( $tmp_category_ids );

            $final_templates[] = $template;
        }

        return $final_templates;
    }

    public function getBlocks() {

        if(!$this->_blocks) {
            $block = new Template_Model_Block();
            $this->_blocks = $block->findByDesign($this->getId());
        }

        return $this->_blocks;

    }

    public function getBlock($name) {

        foreach($this->getBlocks() as $block) {
            if($block->getCode() == $name) return $block;
        }
        return new Template_Model_Block();

    }

    public function getOverview() {
        return Core_Model_Directory::getPathTo(self::PATH_IMAGE.$this->getData('overview'));
    }

    public function getBackgroundImage($base = false) {
        return $base ? Core_Model_Directory::getBasePathTo(self::PATH_IMAGE.$this->getData('background_image')) : Core_Model_Directory::getPathTo($this->getData('background_image'));
    }

    public function getBackgroundImageHd($base = false) {
        return $base ? Core_Model_Directory::getBasePathTo(self::PATH_IMAGE.$this->getData('background_image_hd')) : Core_Model_Directory::getPathTo($this->getData('background_image_hd'));
    }

    public function getBackgroundImageTablet($base = false) {
        return $base ? Core_Model_Directory::getBasePathTo(self::PATH_IMAGE.$this->getData('background_image_tablet')) : Core_Model_Directory::getPathTo($this->getData('background_image_tablet'));
    }

}
