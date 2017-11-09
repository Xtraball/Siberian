<?php

class Template_Model_Block extends Core_Model_Default {

    const PATH_IMAGE = '/images/application';
    const TYPE_APP = 1;
    const TYPE_WHITE_LABEL_EDITOR = 2;
    const TYPE_IONIC_APP = 3;

    protected $_children = array();
    protected $int_validator;
    protected $between_validator;

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Template_Model_Db_Table_Block';
        $this->int_validator = new Zend_Validate_Int();
        $this->between_validator = new Zend_Validate_Between(array('min' => 0, 'max' => 100));
        return $this;
    }

    public function findByDesign($design_id) {
        return $this->getTable()->findByDesign($design_id);
    }

    public function save() {
        if($this->getAppId()) {
            $this->getTable()->saveAppBlock($this);
        } else {
            parent::save();
        }

        return $this;
    }

    public function getName() {
        return $this->_($this->getData('name'));
    }

    public function getBackgroundColor() {
        return $this->getData("background_color") ? $this->getData("background_color") : "transparent";
    }

    public function useImageLink() {
        return $this->getData('use_image_link');
    }

    public function applyToAll() {
        return $this->getData('apply_to_all');
    }

    public function isUniform($block) {
        return
            $this->getId() == $block->getId()
            && ($this->getUseColor() == $block->getUseColor() || ($this->getUseColor() && !$block->getUseColor()))
            && ($this->getUseBackgroundColor() == $block->getUseBackgroundColor() || ($this->getUseBackgroundColor() && !$block->getUseBackgroundColor()))
            && ($this->useImageLink() == $block->useImageLink() || ($this->useImageLink() && !$block->useImageLink()))
        ;
    }

    public function getBackgroundImage($type = null) {
        $background_image = $this->getData('background_image');
        if(!empty($background_image)) {
            if($type == 'normal') $background_image .= '.jpg';
            else if($type == 'retina') $background_image .= '@2x.jpg';
            else if($type == 'retina4') $background_image .= '-568h@2x.jpg';
        }
        else {
            $background_image = '/media/admin/mobile/natif/no-background.png';
        }

        return $background_image;
    }

    public function setBackgroundImage($background_image) {
        $background_image = str_replace(self::PATH_IMAGE, "", $background_image);
        $this->setData('background_image', $background_image);
    }

    public function getImageColor() {
        if($this->getData('image_color')) return $this->getData('image_color');
        else return $this->getColor();
    }

    public function addChild($child) {
        $this->_children[] = $child;
        return $this;
    }

    public function setChildren($children) {
        $this->_children = $children;
        return $this;
    }

    public function getChildren() {
        return $this->_children;
    }

    public function colorize($tile_path, $option, $color = null, $flat = true) {

        if(!is_file($tile_path)) return '';

        // Créé les chemins
        $application = $this->getApplication();
        $dst = '/'.$this->getCode().'/'.$option->getCode().'_'.uniqid().'.png';
        $base_dst = Application_Model_Application::getBaseImagePath().'/'.$dst;

        if(!is_dir(dirname($base_dst))) mkdir(dirname($base_dst), 0777, true);

        if(!$color) $color = $this->getImageColor();
        $color = str_replace('#', '', $color);
        $rgb = $this->toRgb($color);

        list($width, $height) = getimagesize($tile_path);
        $tile = imagecreatefromstring(file_get_contents($tile_path));

        if($tile) {
            for($x=0; $x<$width;$x++) {
                for($y=0;$y<$height;$y++) {
                    $colors = imagecolorat($tile, $x, $y);
                    $current_rgb = imagecolorsforindex($tile, $colors);
                    if($flat) {
                        $color = imagecolorallocatealpha($tile, $rgb['red'], $rgb['green'], $rgb['blue'], $current_rgb['alpha']);
                    }
                    else {
                        $color = imagecolorallocatealpha($tile, $current_rgb['red']*$rgb['red']/255, $current_rgb['green']*$rgb['green']/255, $current_rgb['blue']*$rgb['blue']/255, $current_rgb['alpha']);
                    }
                    imagesetpixel($tile, $x, $y, $color);
                }
            }
            $filename = basename($tile_path);
            imagesavealpha($tile, true);
            if(!imagepng($tile, $base_dst)) {
                $dst = '';
            }
        }

        return $dst;
    }

    public function getColorRGB() {
        return $this->toRgb($this->getData("color"));
    }

    public function getBorderColorRGB(){
        return $this->toRgb($this->getData("border_color"));
    }

    public function getImageColorRGB(){
        return $this->toRgb($this->getData("image_color"));
    }

    public function getBackgroundColorRGB() {
        return $this->toRgb($this->getData("background_color"));
    }

    public function toRgb($hexStr, $returnAsString = false, $seperator = ','){

        $hexStr = preg_replace("/[^0-9A-Fa-f]/", '', $hexStr);
        $rgbArray = array();

        if (strlen($hexStr) == 6) {
            $colorVal = hexdec($hexStr);
            $rgbArray['red'] = 0xFF & ($colorVal >> 0x10);
            $rgbArray['green'] = 0xFF & ($colorVal >> 0x8);
            $rgbArray['blue'] = 0xFF & $colorVal;
        }
        elseif (strlen($hexStr) == 3) {
            $rgbArray['red'] = hexdec(str_repeat(substr($hexStr, 0, 1), 2));
            $rgbArray['green'] = hexdec(str_repeat(substr($hexStr, 1, 1), 2));
            $rgbArray['blue'] = hexdec(str_repeat(substr($hexStr, 2, 1), 2));
        }
        else {
            return false;
        }

        return $returnAsString ? implode($seperator, $rgbArray) : $rgbArray;
    }

    /**
     * Verifies the presence of the text_opacity parameter and validates it. If all is well it sets the text_opacity property.
     * Must be a float between 0 and 1
     * PS: A possible source of confusion is where these values are saved: They are saved in the table template_block_app.
     *
     * @param $colors
     * @return $this
     */
    public function setTextOpacity($colors) {
        if (isset($colors['text_opacity'])) {
            $opacity = $colors['text_opacity']*1;
            if ($opacity>=0 AND $opacity<=100) {
                $this->setData('text_opacity', $opacity);
            }
        }
        return $this;
    }

    /**
     * Verifies the presence of the background_opacity parameter and validates it. If all is well it sets the background_opacity property.
     * Must be a float between 0 and 1
     * PS: A possible source of confusion is where these values are saved: They are saved in the table template_block_app.
     *
     * @param $colors
     * @return $this
     */
    public function setBackgroundOpacity($colors) {
        if (isset($colors['background_opacity'])) {
            $opacity = $colors['background_opacity']*1;
            if ($opacity>=0 AND $opacity<=100) {
                $this->setData('background_opacity', $opacity);
            }
        }
        return $this;
    }

    /**
     * Verifies the presence of the border_opacity parameter and validates it. If all is well it sets the border_opacity property.
     * Must be a float between 0 and 1
     * PS: A possible source of confusion is where these values are saved: They are saved in the table template_block_app.
     *
     * @param $colors
     * @return $this
     */
    public function setBorderOpacity($colors) {
        if (isset($colors['border_opacity'])) {
            $opacity = $colors['border_opacity']*1;
            if ($opacity>=0 AND $opacity<=100) {
                $this->setData('border_opacity', $opacity);
            }
        }
        return $this;
    }

    /**
     * Verifies the presence of the image_opacity parameter and validates it. If all is well it sets the image_opacity property.
     * Must be a float between 0 and 1
     * PS: A possible source of confusion is where these values are saved: They are saved in the table template_block_app.
     *
     * @param $colors
     * @return $this
     */
    public function setImageOpacity($colors) {
        if (isset($colors['image_opacity'])) {
            $opacity = $colors['image_opacity']*1;
            if ($opacity>=0 AND $opacity<=100) {
                $this->setData('image_opacity', $opacity);
            }
        }
        return $this;
    }

    /**
     * Returns and rgba(red, green, blue, opacity) from the background_color and background_opacity
     *
     * @return string
     */
    public function getBackgroundColorRGBA() {
        // has the form array('red' => ..., 'green' => ..., 'blue' => ...)
        $background_color = $this->getBackgroundColorRGB();
        // If the value is null then 1 by default
        $opacity = $this->getData('background_opacity') / 100;
        // Yields a string 'rgba(red,green,blue,opacity)'
        $rgba = 'rgba(' . $background_color['red'] . ',' . $background_color['green'] . ',' . $background_color['blue'] . ',' . $opacity . ')';
        return $rgba;
    }

    /**
     * Returns and rgba(red, green, blue, opacity) from the color and text_opacity
     *
     * @return string
     */
    public function getColorRGBA() {
        // has the form array('red' => ..., 'green' => ..., 'blue' => ...)
        $text_color = $this->getColorRGB();
        // If the value is null then 1 by default
        $opacity = $this->getData('text_opacity') / 100;
        // Yields a string 'rgba(red,green,blue,opacity)'
        $rgba = 'rgba(' . $text_color['red'] . ',' . $text_color['green'] . ',' . $text_color['blue'] . ',' . $opacity . ')';
        return $rgba;
    }

    /**
     * Returns and rgba(red, green, blue, opacity) from the image_color and image_opacity
     *
     * @return string
     */
    public function getImageColorRGBA() {
        // has the form array('red' => ..., 'green' => ..., 'blue' => ...)
        $image_color = $this->getImageColorRGB();
        // If the value is null then 1 by default
        $opacity = $this->getData('image_opacity') / 100;
        // Yields a string 'rgba(red,green,blue,opacity)'
        $rgba = 'rgba(' . $image_color['red'] . ',' . $image_color['green'] . ',' . $image_color['blue'] . ',' . $opacity . ')';
        return $rgba;
    }

    /**
     * Returns and rgba(red, green, blue, opacity) from the border_color and border_opacity
     *
     * @return string
     */
    public function getBorderColorRGBA() {
        // has the form array('red' => ..., 'green' => ..., 'blue' => ...)
        $border_color = $this->getBorderColorRGB();
        // If the value is null then 1 by default
        $opacity = $this->getData('border_opacity') / 100;
        // Yields a string 'rgba(red,green,blue,opacity)'
        $rgba = 'rgba(' . $border_color['red'] . ',' . $border_color['green'] . ',' . $border_color['blue'] . ',' . $opacity . ')';
        return $rgba;
    }

}
