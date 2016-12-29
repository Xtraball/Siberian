<?php

class Template_Model_Design extends Core_Model_Default {

    const PATH_IMAGE = '/images/templates';

    public static $variables = array();

    protected $_blocks;

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Template_Model_Db_Table_Design';
        return $this;
    }

    /**
     * @param $variables
     */
    public static function registerVariables($variables) {
        if(!is_array($variables)) {
            $variables = array($variables);
        }
        foreach($variables as $variable) {
            self::$variables[] = $variable;
        }
    }

    public static function getCssPath($application) {

        /** Determines if the App has been updated or not. */
        $block_app = new Template_Model_Block_App();

        $path = Core_Model_Directory::getPathTo("var/cache/css");
        $base_path = Core_Model_Directory::getBasePathTo("var/cache/css");
        $file = $application->getId().".css";
        if(!is_file("{$base_path}/{$file}")) {
            /** Determines if the App has been updated or not. */
            $block_app = new Template_Model_Block_App();
            $new_scss = $block_app->isNewScss($application->getId());

            self::generateCss($application, false, false, $new_scss);
        }

        return "{$path}/{$file}";

    }

    /**
     * @param $application
     * @return array
     */
    public static function getVariables($application) {
        return self::generateCss($application, false, true, true);
    }

    public static function generateCss($application, $javascript = false, $return_variables = false, $new_scss = false) {

        $variables = array();
        $blocks = $application->getBlocks();

        if(!$javascript) {
            foreach ($blocks as $block) {

                if ($block->getColorVariableName() AND $block->getColorRGBA()) {
                    $variables[$block->getColorVariableName()] = $block->getColorRGBA();
                }
                if ($block->getBackgroundColorVariableName() AND $block->getBackgroundColorRGBA()) {
                    $variables[$block->getBackgroundColorVariableName()] = $block->getBackgroundColorRGBA();
                }
                if ($block->getBorderColorVariableName() AND $block->getBorderColorRGBA()) {
                    $variables[$block->getBorderColorVariableName()] = $block->getBorderColorRGBA();
                }

                foreach ($block->getChildren() as $child) {
                    if ($child->getColorVariableName() AND $child->getColorRGBA()) {
                        $variables[$child->getColorVariableName()] = $child->getColorRGBA();
                    }
                    if ($child->getBackgroundColorVariableName() AND $child->getBackgroundColorRGBA()) {
                        $variables[$child->getBackgroundColorVariableName()] = $child->getBackgroundColorRGBA();
                    }
                    if ($child->getBorderColorVariableName() AND $child->getBorderColorRGBA()) {
                        $variables[$child->getBorderColorVariableName()] = $child->getBorderColorRGBA();
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

        $scss_files = array(
            "ionic.siberian.variables.scss",
            "ionic.siberian.style.scss"
        );

        # Do not break old apps css rebuild.
        if($new_scss) {
            $scss_files = array(
                "ionic.siberian.variables-opacity.scss",
                "ionic.siberian.style.scss"
            );
        }

        foreach($scss_files as $file) {
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

        /** Return only vars */
        if($return_variables) {
            return $variables;
        }

        $scss = implode("\n", $content);

        /** With custom from app */
        $custom_app = $scss;
        if(!$javascript) {
            $custom_app = $scss."\n".$application->getCustomScss();
        }

        $compiler = Siberian_Scss::getCompiler();
        $compiler->addImportPath(Core_Model_Directory::getBasePathTo("var/apps/browser/lib/ionic/scss"));
        $compiler->addImportPath(Core_Model_Directory::getBasePathTo("var/apps/browser/scss"));

        $result = true;
        try {
            $css = $compiler->compile('
                @import "_variables.scss";
                @import "_mixins.scss";
                ' . $custom_app
            );
        } catch(Exception $e) {
            /** Meanwhile, fallback without custom scss */
            $css = $compiler->compile('
                @import "_variables.scss";
                @import "_mixins.scss";
                ' . $scss
            );
            $result = false;
        }

        if($javascript) {
            return $css;
        } else {
            $folder = Core_Model_Directory::getBasePathTo("var/cache/css");
            $file = $application->getId().".css";
            if(!is_dir($folder)) {
                mkdir($folder, 0777, true);
            }
            file_put_contents("{$folder}/{$file}", $css);
        }

        return $result;
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

    public function getOverview($data_key = null) {
        $data = (empty($data_key)) ? $this->getData('overview') : $this->getData($data_key);
        return Core_Model_Directory::getPathTo(self::PATH_IMAGE.$data);
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
