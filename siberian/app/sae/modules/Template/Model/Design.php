<?php

use Siberian\File;
use Siberian\Request;

/**
 * Class Template_Model_Design
 */
class Template_Model_Design extends Core_Model_Default
{

    /**
     *
     */
    const PATH_IMAGE = '/images/templates';

    /**
     * @var array
     */
    public static $variables = [];

    public static $lastException = null;

    /**
     * @var
     */
    protected $_blocks;

    /**
     * Template_Model_Design constructor.
     * @param array $params
     * @throws Zend_Exception
     */
    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->_db_table = 'Template_Model_Db_Table_Design';
        return $this;
    }

    /**
     * @param $variables
     */
    public static function registerVariables($variables)
    {
        if (!is_array($variables)) {
            $variables = [$variables];
        }
        foreach ($variables as $variable) {
            self::$variables[] = $variable;
        }
    }

    /**
     * @param $application
     * @return string
     */
    public static function getCssPath($application)
    {
        /** Determines if the App has been updated or not. */

        $path = rpath("var/cache/css");
        $basePath = path("var/cache/css");
        $file = $application->getId() . ".css";

        $rebuild = filter_var($application->getGenerateScss(), FILTER_VALIDATE_BOOLEAN);

        // If we should regen the SCSS!
        if (!is_file("{$basePath}/{$file}") || $rebuild) {
            $application
                ->setGenerateScss(0)
                ->save();

            self::generateCss($application, false, false);
        }

        return "{$path}/{$file}";

    }

    /**
     * @param $application
     * @return array
     */
    public static function getVariables($application)
    {
        return self::generateCss($application, false, true, true);
    }

    /**
     * @param Application_Model_Application $application
     * @param bool $javascript
     * @param bool $return_variables
     * @param bool $new_scss
     * @return bool|string|array
     */
    public static function generateCss($application, $javascript = false, $return_variables = false, $new_scss = true)
    {

        $variables = [];
        $blocks = $application->getBlocks();

        if (!$javascript) {
            foreach ($blocks as $block) {

                if ($block->getColorVariableName() && $block->getColorRGBA()) {
                    $variables[$block->getColorVariableName()] = $block->getColorRGBA();
                }
                if ($block->getBackgroundColorVariableName() && $block->getBackgroundColorRGBA()) {
                    $variables[$block->getBackgroundColorVariableName()] = $block->getBackgroundColorRGBA();
                }
                if ($block->getBorderColorVariableName() && $block->getBorderColorRGBA()) {
                    $variables[$block->getBorderColorVariableName()] = $block->getBorderColorRGBA();
                }
                if ($block->getImageColorVariableName() && $block->getImageColorRGBA()) {
                    $variables[$block->getImageColorVariableName()] = $block->getImageColorRGBA();
                }

                foreach ($block->getChildren() as $child) {
                    if ($child->getColorVariableName() && $child->getColorRGBA()) {
                        $variables[$child->getColorVariableName()] = $child->getColorRGBA();
                    }
                    if ($child->getBackgroundColorVariableName() && $child->getBackgroundColorRGBA()) {
                        $variables[$child->getBackgroundColorVariableName()] = $child->getBackgroundColorRGBA();
                    }
                    if ($child->getBorderColorVariableName() && $child->getBorderColorRGBA()) {
                        $variables[$child->getBorderColorVariableName()] = $child->getBorderColorRGBA();
                    }
                    if ($child->getImageColorVariableName() && $child->getImageColorRGBA()) {
                        $variables[$child->getImageColorVariableName()] = $child->getImageColorRGBA();
                    }
                }

            }
        } else {
            foreach ($blocks as $block) {

                $block_id = (strlen(dechex($block->getId())) == 2) ? dechex($block->getId()) : "0" . dechex($block->getId());

                if ($block->getColorVariableName() && $block->getColor()) {
                    $block_pos = "01";
                    $hex = "#" . $block_id . "00" . $block_pos;

                    $variables[$block->getColorVariableName()] = $hex;
                }
                if ($block->getBackgroundColorVariableName() && $block->getBackgroundColor()) {
                    $block_pos = "02";
                    $hex = "#" . $block_id . "00" . $block_pos;

                    $variables[$block->getBackgroundColorVariableName()] = $hex;
                }
                if ($block->getBorderColorVariableName() && $block->getBorderColor()) {
                    $block_pos = "03";
                    $hex = "#" . $block_id . "00" . $block_pos;

                    $variables[$block->getBorderColorVariableName()] = $hex;
                }

                if ($block->getImageColorVariableName() && $block->getImageColor()) {
                    $block_pos = "04";
                    $hex = "#" . $block_id . "00" . $block_pos;

                    $variables[$block->getImageColorVariableName()] = $hex;
                }

                foreach ($block->getChildren() as $child) {
                    $child_id = (strlen(dechex($child->getId())) == 2) ? dechex($child->getId()) : "0" . dechex($child->getId());

                    if ($child->getColorVariableName() && $child->getColor()) {
                        $child_pos = "01";
                        $hex = "#" . $block_id . $child_id . $child_pos;

                        $variables[$child->getColorVariableName()] = $hex;
                    }
                    if ($child->getBackgroundColorVariableName() && $child->getBackgroundColor()) {
                        $child_pos = "02";
                        $hex = "#" . $block_id . $child_id . $child_pos;

                        $variables[$child->getBackgroundColorVariableName()] = $hex;
                    }
                    if ($child->getBorderColorVariableName() && $child->getBorderColor()) {
                        $child_pos = "03";
                        $hex = "#" . $block_id . $child_id . $child_pos;

                        $variables[$child->getBorderColorVariableName()] = $hex;
                    }
                }

            }

        }


        // Prepend google font
        $fontFamily = $application->getFontFamily();
        $fontImport = "";
        if (!empty($fontFamily)) {
            $replace = str_replace("+", " ", $fontFamily);

            $fontImport = Request::get("https://fonts.googleapis.com/css?family={$fontFamily}", [
                "subset" => "latin,greek,cyrillic",
            ], null, null, [
                "User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.80 Safari/537.36"
            ]);

            if (Request::$statusCode == 200) {
                $variables['$font-family'] = "'$replace', sans-serif";
            } else {
                $fontImport = "/** Unable to fetch Google Font {$fontFamily} */";
            }
        }

        $content = [];

        $scss_files = [
            "ionic.siberian.variables-opacity.scss",
            "ionic.siberian.style.scss"
        ];

        foreach ($scss_files as $file) {
            $f = fopen(path("var/apps/browser/scss/{$file}"), "r");
            if ($f) {
                while (($line = fgets($f)) !== false) {
                    preg_match("/([\$a-zA-Z0-9_-]*)/", $line, $matches);
                    if (!empty($matches[0]) && !empty($variables[$matches[0]])) {
                        $line = "{$matches[0]}: {$variables[$matches[0]]} !default;";
                    }
                    $content[] = $line;
                }
            }
        }

        /** Return only vars */
        if ($return_variables) {
            return $variables;
        }

        $scss = implode_polyfill("\n", $content);

        /** With custom from app */
        $custom_app = $scss;
        if (!$javascript) {
            $custom_app = $scss . "\n" . $application->getCustomScss();
        }

        $compiler = Siberian_Scss::getCompiler();
        $compiler->addImportPath(path("var/apps/browser/lib/ionic/scss"));
        $compiler->addImportPath(path("var/apps/browser/scss"));

        // Import custom modules SCSS files!
        foreach (Siberian_Assets::$assets_scss as $scssFile) {
            $path = path($scssFile);
            if (!is_file($path)) {
                continue;
            }
            $custom_app .= file_get_contents($path);
        }

        $result = true;
        try {
            $css = $compiler->compile('
                @import "_variables.scss";
                @import "_mixins.scss";
                ' . $custom_app
            );
        } catch (\Exception $e) {
            /** Meanwhile, fallback without custom scss */
            $css = $compiler->compile('
                @import "_variables.scss";
                @import "_mixins.scss";
                ' . $scss
            );
            $result = false;
            self::$lastException = $e->getMessage();
        }

        $css = $fontImport . "\n" . $css;

        if ($javascript) {
            return $css;
        }

        $folder = path("var/cache/css");
        $file = $application->getId() . ".css";
        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }
        File::putContents("{$folder}/{$file}", $css);

        return $result;
    }

    /**
     * @param null $where
     * @return array
     */
    public function findAllWithCategory($where = null)
    {
        $all_templates = $this->findAll($where, ['position ASC', 'name ASC']);
        $template_a_category = $this->getTable()->findAllWithCategory();
        $final_templates = [];

        foreach ($all_templates as $template) {

            $tmp_category_ids = [];
            foreach ($template_a_category as $template_category) {
                if ($template->getDesignId() == $template_category["design_id"])
                    $tmp_category_ids[] = $template_category["category_id"];
            }
            $template->setCategoryIds($tmp_category_ids);

            $final_templates[] = $template;
        }

        return $final_templates;
    }

    /**
     * @return mixed
     * @throws Zend_Exception
     * @throws Zend_Validate_Exception
     */
    public function getBlocks()
    {

        if (!$this->_blocks) {
            $block = new Template_Model_Block();
            $this->_blocks = $block->findByDesign($this->getId());
        }

        return $this->_blocks;

    }

    /**
     * @param $name
     * @return mixed|Template_Model_Block
     * @throws Zend_Exception
     * @throws Zend_Validate_Exception
     */
    public function getBlock($name)
    {

        foreach ($this->getBlocks() as $block) {
            if ($block->getCode() == $name) return $block;
        }
        return new Template_Model_Block();

    }

    /**
     * @param null $data_key
     * @return string
     */
    public function getOverview($data_key = null)
    {
        $data = (empty($data_key)) ?
            $this->getData('overview') : $this->getData($data_key);

        if ($this->getVersion() == 2) {
            return Core_Model_Directory::getPathTo($data);
        }
        return Core_Model_Directory::getPathTo(self::PATH_IMAGE . $data);
    }

    /**
     * @param bool $base
     * @return string
     */
    public function getBackgroundImage($base = false)
    {
        return $base ? Core_Model_Directory::getBasePathTo(self::PATH_IMAGE . $this->getData('background_image')) : Core_Model_Directory::getPathTo($this->getData('background_image'));
    }

    /**
     * @param bool $base
     * @return string
     */
    public function getBackgroundImageHd($base = false)
    {
        return $base ? Core_Model_Directory::getBasePathTo(self::PATH_IMAGE . $this->getData('background_image_hd')) : Core_Model_Directory::getPathTo($this->getData('background_image_hd'));
    }

    /**
     * @param bool $base
     * @return string
     */
    public function getBackgroundImageTablet($base = false)
    {
        return $base ? Core_Model_Directory::getBasePathTo(self::PATH_IMAGE . $this->getData('background_image_tablet')) : Core_Model_Directory::getPathTo($this->getData('background_image_tablet'));
    }

}
