<?php

/**
 * Class Template_Model_Block
 *
 * @method string getColor
 * @method $this setBlockId(integer $blockId)
 * @method $this setAppId(integer $appId)
 */
class Template_Model_Block extends Core_Model_Default
{

    /**
     *
     */
    const PATH_IMAGE = '/images/application';
    /**
     *
     */
    const TYPE_APP = 1;
    /**
     *
     */
    const TYPE_WHITE_LABEL_EDITOR = 2;
    /**
     *
     */
    const TYPE_IONIC_APP = 3;

    /**
     * @var array
     */
    protected $_children = [];
    /**
     * @var Zend_Validate_Int
     */
    protected $int_validator;
    /**
     * @var Zend_Validate_Between
     */
    protected $between_validator;

    /**
     * Template_Model_Block constructor.
     * @param array $params
     * @throws Zend_Exception
     * @throws Zend_Validate_Exception
     */
    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->_db_table = 'Template_Model_Db_Table_Block';
        $this->int_validator = new Zend_Validate_Int();
        $this->between_validator = new Zend_Validate_Between(['min' => 0, 'max' => 100]);
        return $this;
    }

    /**
     * @param $design_id
     * @return mixed
     */
    public function findByDesign($design_id)
    {
        return $this->getTable()->findByDesign($design_id);
    }

    /**
     * @return $this
     */
    public function save()
    {
        if ($this->getAppId()) {
            $this->getTable()->saveAppBlock($this);
        } else {
            // Not saving empty block!
            if (empty($this->getTypeId()) || empty($this->getCode())) {
                return $this;
            }
            parent::save();
        }

        return $this;
    }

    /**
     * @return mixed|string
     */
    public function getName()
    {
        return __($this->getData('name'));
    }

    /**
     * @return array|mixed|null|string
     */
    public function getBackgroundColor()
    {
        return $this->getData("background_color") ? $this->getData("background_color") : "transparent";
    }

    /**
     * @return array|mixed|null|string
     */
    public function useImageLink()
    {
        return $this->getData('use_image_link');
    }

    /**
     * @return array|mixed|null|string
     */
    public function applyToAll()
    {
        return $this->getData('apply_to_all');
    }

    /**
     * @param $block
     * @return bool
     */
    public function isUniform($block)
    {
        return
            $this->getId() == $block->getId()
            && ($this->getUseColor() == $block->getUseColor() || ($this->getUseColor() && !$block->getUseColor()))
            && ($this->getUseBackgroundColor() == $block->getUseBackgroundColor() || ($this->getUseBackgroundColor() && !$block->getUseBackgroundColor()))
            && ($this->useImageLink() == $block->useImageLink() || ($this->useImageLink() && !$block->useImageLink()));
    }

    /**
     * @param null $type
     * @return array|mixed|null|string
     */
    public function getBackgroundImage($type = null)
    {
        $background_image = $this->getData('background_image');
        if (!empty($background_image)) {
            if ($type == 'normal') $background_image .= '.jpg';
            else if ($type == 'retina') $background_image .= '@2x.jpg';
            else if ($type == 'retina4') $background_image .= '-568h@2x.jpg';
        } else {
            $background_image = '/media/admin/mobile/natif/no-background.png';
        }

        return $background_image;
    }

    /**
     * @param $background_image
     */
    public function setBackgroundImage($background_image)
    {
        $background_image = str_replace(self::PATH_IMAGE, "", $background_image);
        $this->setData('background_image', $background_image);
    }

    /**
     * @return array|mixed|null|string
     */
    public function getImageColor()
    {
        if ($this->getData('image_color')) return $this->getData('image_color');
        else return $this->getColor();
    }

    /**
     * @param $child
     * @return $this
     */
    public function addChild($child)
    {
        $this->_children[] = $child;
        return $this;
    }

    /**
     * @param $children
     * @return $this
     */
    public function setChildren($children)
    {
        $this->_children = $children;
        return $this;
    }

    /**
     * @return array
     */
    public function getChildren()
    {
        return $this->_children;
    }

    /**
     * @param $tile_path
     * @param $option
     * @param null $color
     * @param bool $flat
     * @return string
     */
    public function colorize($tile_path, $option, $color = null, $flat = true)
    {

        if (!is_file($tile_path)) return '';

        // Créé les chemins
        $application = $this->getApplication();
        $dst = '/' . $this->getCode() . '/' . $option->getCode() . '_' . uniqid() . '.png';
        $base_dst = Application_Model_Application::getBaseImagePath() . '/' . $dst;

        if (!is_dir(dirname($base_dst))) mkdir(dirname($base_dst), 0777, true);

        if (!$color) $color = $this->getImageColor();
        $color = str_replace('#', '', $color);
        $rgb = $this->toRgb($color);

        list($width, $height) = getimagesize($tile_path);
        $tile = imagecreatefromstring(file_get_contents($tile_path));

        if ($tile) {
            for ($x = 0; $x < $width; $x++) {
                for ($y = 0; $y < $height; $y++) {
                    $colors = imagecolorat($tile, $x, $y);
                    $current_rgb = imagecolorsforindex($tile, $colors);
                    if ($flat) {
                        $color = imagecolorallocatealpha($tile, $rgb['red'], $rgb['green'], $rgb['blue'], $current_rgb['alpha']);
                    } else {
                        $color = imagecolorallocatealpha($tile, $current_rgb['red'] * $rgb['red'] / 255, $current_rgb['green'] * $rgb['green'] / 255, $current_rgb['blue'] * $rgb['blue'] / 255, $current_rgb['alpha']);
                    }
                    imagesetpixel($tile, $x, $y, $color);
                }
            }
            $filename = basename($tile_path);
            imagesavealpha($tile, true);
            if (!imagepng($tile, $base_dst)) {
                $dst = '';
            }
        }

        return $dst;
    }

    /**
     * Generic method to set from RGBA
     *
     * @param $key
     * @param $rgba
     *
     * @return $this
     */
    public function setFromRgba($key, $rgba)
    {
        $parsed = self::rgbaToArray($rgba);
        switch ($key) {
            case "color":
                $this
                    ->setColor($parsed["hex"])
                    ->setTextOpacity($parsed["alpha"])
                    ->save();
                break;
            case "background_color":
                $this
                    ->setBackgroundColor($parsed["hex"])
                    ->setBackgroundOpacity($parsed["alpha"])
                    ->save();
                break;
            case "border_color":
                $this
                    ->setBorderColor($parsed["hex"])
                    ->setBorderOpacity($parsed["alpha"])
                    ->save();
                break;
            case "image_color":
                $this
                    ->setImageColor($parsed["hex"])
                    ->setImageOpacity($parsed["alpha"])
                    ->save();
                break;
        }

        return $this;
    }

    /**
     * @param $rgba
     * @return array
     */
    public static function rgbaToArray ($rgba)
    {
        $cleaned = preg_replace("/[^\d,.]/", "", $rgba);
        $parts = explode(",", $cleaned);

        $hexR = str_pad(dechex($parts[0]), 2, "0", STR_PAD_LEFT);
        $hexG = str_pad(dechex($parts[1]), 2, "0", STR_PAD_LEFT);
        $hexB = str_pad(dechex($parts[2]), 2, "0", STR_PAD_LEFT);

        $rgbaParts = [
            "r" => $parts[0],
            "g" => $parts[1],
            "b" => $parts[2],
            "a" => $parts[3],
            "hex" => "#{$hexR}{$hexG}{$hexB}",
            "alpha" => $parts[3] * 100,
        ];

        return $rgbaParts;
    }

    /**
     * @return array|bool|string
     */
    public function getColorRGB()
    {
        return $this->toRgb($this->getData("color"));
    }

    /**
     * @return array|bool|string
     */
    public function getBorderColorRGB()
    {
        return $this->toRgb($this->getData("border_color"));
    }

    /**
     * @return array|bool|string
     */
    public function getImageColorRGB()
    {
        return $this->toRgb($this->getData("image_color"));
    }

    /**
     * @return array|bool|string
     */
    public function getBackgroundColorRGB()
    {
        return $this->toRgb($this->getData("background_color"));
    }

    /**
     * @param $hexStr
     * @param bool $returnAsString
     * @param string $seperator
     * @return array|bool|string
     */
    public function toRgb($hexStr, $returnAsString = false, $seperator = ',')
    {

        $hexStr = preg_replace("/[^0-9A-Fa-f]/", '', $hexStr);
        $rgbArray = [];

        if (strlen($hexStr) == 6) {
            $colorVal = hexdec($hexStr);
            $rgbArray['red'] = 0xFF & ($colorVal >> 0x10);
            $rgbArray['green'] = 0xFF & ($colorVal >> 0x8);
            $rgbArray['blue'] = 0xFF & $colorVal;
        } elseif (strlen($hexStr) == 3) {
            $rgbArray['red'] = hexdec(str_repeat(substr($hexStr, 0, 1), 2));
            $rgbArray['green'] = hexdec(str_repeat(substr($hexStr, 1, 1), 2));
            $rgbArray['blue'] = hexdec(str_repeat(substr($hexStr, 2, 1), 2));
        } else {
            return false;
        }

        return $returnAsString ? implode_polyfill($seperator, $rgbArray) : $rgbArray;
    }

    /**
     * Returns and rgba(red, green, blue, opacity) from the background_color and background_opacity
     *
     * @return string
     */
    public function getBackgroundColorRGBA()
    {
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
    public function getColorRGBA()
    {
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
    public function getImageColorRGBA()
    {
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
    public function getBorderColorRGBA()
    {
        // has the form array('red' => ..., 'green' => ..., 'blue' => ...)
        $border_color = $this->getBorderColorRGB();
        // If the value is null then 1 by default
        $opacity = $this->getData('border_opacity') / 100;
        // Yields a string 'rgba(red,green,blue,opacity)'
        $rgba = 'rgba(' . $border_color['red'] . ',' . $border_color['green'] . ',' . $border_color['blue'] . ',' . $opacity . ')';
        return $rgba;
    }

}
