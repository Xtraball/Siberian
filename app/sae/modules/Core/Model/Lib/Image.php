<?php

class Core_Model_Lib_Image {

    protected $_id;

    protected $_path;

    protected $_extension;

    protected $_color;

    protected $_width;

    protected $_height;

    protected $_crop = false;

    protected $_resources;

    protected $_content;

    protected $_cache_dir;

    public function __construct() {
        $this->_cache_dir = Core_Model_Directory::getImageCacheDirectory(true).'/';
        if(!is_dir($this->_cache_dir)) {
            mkdir($this->_cache_dir, 0777, true);
        }
        return $this;
    }

    public function colorize() {

        $image_name = "{$this->_id}.png";
        $this->_extension = "png";

        $cache = $this->_cache_dir;

        if((!file_exists($cache.$image_name) OR !$this->getResources()) && file_exists($this->_path)) {

            try {
                list($width, $height) = getimagesize($this->_path);

                $img = imagecreatefromstring(file_get_contents($this->_path));
                $img2 = imagecreatetruecolor($width, $height);
                imagesavealpha($img2, true);
                imagealphablending($img2, false);
                imagecopyresampled($img2, $img, 0, 0, 0, 0, $width, $height, $width, $height);
                /** Testing if this is a resource. */
                if(is_resource($img2) && ($img2 !== false) && is_resource($img)) {
                    if($this->_color) {
                        $rgb = $this->_toRgb($this->_color);

                        for($y=0; $y<$height; $y++) {
                            for($x=0; $x<$width; $x++) {

                                $colors = imagecolorat($img, $x, $y);
                                $current_rgb = imagecolorsforindex($img, $colors);
                                $color = imagecolorallocatealpha($img2, $rgb['red'], $rgb['green'], $rgb['blue'], $current_rgb['alpha']);
                                imagesetpixel($img2, $x, $y, $color);

                            }
                        }
                    }

                    imagesavealpha($img2, true);
                    imagepng($img2, $cache.$image_name);

                    Siberian_Media::optimize($cache.$image_name, true);

                    $this->_resources = $img2;
                }

            } catch(Exception $e) {
                throw new $e;
            }

        }

        return $this;

    }

    /**
     * Colorize image in place.
     *
     * @param $path
     * @param string $color
     * @throws Siberian_Exception
     */
    public static function sColorize($path, $color = "000000") {
        if(file_exists($path)) {
            try {
                list($width, $height) = getimagesize($path);

                $img = imagecreatefromstring(file_get_contents($path));
                $img2 = imagecreatetruecolor($width, $height);
                imagesavealpha($img2, true);
                imagealphablending($img2, false);
                imagecopyresampled($img2, $img, 0, 0, 0, 0, $width, $height, $width, $height);

                /** Testing if this is a resource. */
                if(is_resource($img2) && ($img2 !== false) && is_resource($img)) {
                    $rgb = self::sToRgb($color);

                    for($y=0; $y<$height; $y++) {
                        for($x=0; $x<$width; $x++) {

                            $colors = imagecolorat($img, $x, $y);
                            $current_rgb = imagecolorsforindex($img, $colors);
                            $color = imagecolorallocatealpha($img2, $rgb["red"], $rgb["green"], $rgb["blue"], $current_rgb["alpha"]);
                            imagesetpixel($img2, $x, $y, $color);

                        }
                    }

                    imagesavealpha($img2, true);
                    imagepng($img2, $path);
                }

            } catch(Exception $e) {
                throw new Siberian_Exception($e->getMessage());
            }
        }
    }

    /**
     * No image should be null and not an empty string
     *
     * @param $name
     * @param bool $base
     * @return null|string
     */
    public static function sGetImage($name, $base = false) {

        if(file_exists(Core_Model_Directory::getDesignPath(true) . "/images/" . $name)) {

            return Core_Model_Directory::getDesignPath($base).'/images/'.$name;

        } else if(file_exists(Media_Model_Library_Image::getBaseImagePathTo($name))) {

            return $base ? Media_Model_Library_Image::getBaseImagePathTo($name) :
                Media_Model_Library_Image::getImagePathTo($name);

        }

        return null;
    }

    public function crop() {

        $extension = pathinfo($this->_path, PATHINFO_EXTENSION);
        if(!in_array($extension, array("jpg", "jpeg", "png", "gif"))) $extension = "png";

        $this->_extension = $extension;

        $image_name = "{$this->_id}.{$extension}";

        $cache = $this->_cache_dir;

        if($this->_canCrop() AND (!file_exists($cache.$image_name) OR !$this->getResources())) {

            try {
                $newWidth = $this->_width ? $this->_width : $this->_height;
                $newHeight = $this->_height ? $this->_height : $this->_width;

                list($width, $height) = getimagesize($this->_path);
                $img = imagecreatefromstring(file_get_contents($this->_path));
                $newIcon = imagecreatetruecolor($newWidth, $newHeight);
                imagealphablending($newIcon, false);

                imagecopyresampled($newIcon, $img, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagesavealpha($newIcon, true);

                if(in_array($extension, array('jpg', 'jpeg'))) {
                    imagejpeg($newIcon, $cache.$image_name, 90);
                } else if($extension == 'png') {
                    imagepng($newIcon, $cache.$image_name);
                } else if($extension == 'gif') {
                    imagegif($newIcon, $cache.$image_name);
                }

                Siberian_Media::optimize($cache.$image_name, true);

                $this->_resources = $newIcon;

            } catch(Exception $e) {
                throw new $e;
            }

        }

        return $this;

    }

    public function setId($id) {
        $this->_id = $id;
        return $this;
    }

    public function setPath($path) {
        $this->_path = $path;
        return $this;
    }

    public function getImagePath() {
        return $this->_cache_dir.$this->_id.".".$this->_extension;
    }

    public function setColor($color) {
        $this->_color = $color;
        return $this;
    }

    public function setWidth($width) {
        $this->_width = $width;
        return $this;
    }

    public function setHeight($height) {
        $this->_height = $height;
        return $this;
    }

    public function setCrop($crop) {
        $this->_crop = $crop;
        return $this;
    }

    public function getResources() {

        if($this->_isValid() AND !$this->_resources) {
            $this->_resources = imagecreatefromstring(file_get_contents(Core_Model_Directory::getBasePathTo($this->getImagePath())));
            imagesavealpha($this->_resources, true);
        }
        return $this->_resources;
    }

    public function getUrl($base = false) {
        return $base ? $this->getImagePath() : Core_Model_Directory::getPathTo(str_replace(Core_Model_Directory::getBasePathTo(), '', $this->getImagePath()));
    }

    public function __toString() {

        if($this->_isValid() AND !$this->_content) {
            $this->_content = file_get_contents($this->getImagePath());
        }
        return $this->_content;
    }

    public static function getMimeType($image_path) {

        $mimetype = false;

        if(function_exists('finfo_fopen')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimetype = finfo_file($finfo, $image_path);
            finfo_close($finfo);
        } elseif(function_exists('exif_imagetype')) {
            $mimetype = exif_imagetype($image_path);
            switch($mimetype) {
                case IMAGETYPE_PNG: $mimetype = "image/png"; break;
                case IMAGETYPE_JPEG: $mimetype = "image/jpeg"; break;
                case IMAGETYPE_GIF: $mimetype = "image/gif"; break;
                default: break;
            }
        } elseif(function_exists('mime_content_type')) {
           $mimetype = mime_content_type($image_path);
        } elseif(function_exists('getimagesize')) {
            $image_size = getimagesize($image_path);
            $mimetype = !empty($image_size["mime"]) ? $image_size["mime"] : null;
        }

        return $mimetype;
    }

    public static function getColorizedUrl($image_id, $color) {

        $color = str_replace('#', '', $color);
        $id = md5(implode('+', array($image_id, $color)));
        $url = '';

        $image = new Media_Model_Library_Image();
        if(is_numeric($image_id)) {
            $image->find($image_id);
            if(!$image->getId()) return $url;
            if(!$image->getCanBeColorized()) $color = null;
            $path = $image->getLink();
            $path = Media_Model_Library_Image::getBaseImagePathTo($path, $image->getAppId());
        } else if(!Zend_Uri::check($image_id) AND stripos($image_id, Core_Model_Directory::getBasePathTo()) === false) {
            $path = Core_Model_Directory::getBasePathTo($image_id);
        } else {
            $path = $image_id;
        }

        try {
            $image = new self();
            $image->setId($id)
                ->setPath($path)
                ->setColor($color)
                ->colorize()
            ;
            $url = $image->getUrl();
        } catch(Exception $e) {
            $url = '';
        }

        return $url;
    }

    protected function _isValid() {
        return $this->_id && $this->_path && file_exists($this->_path);
    }

    protected function _canCrop() {
        return $this->_width || $this->_height;
    }

    /**
     * @deprecated
     *
     * @param $hexStr
     * @param bool $returnAsString
     * @param string $seperator
     * @return array|bool|string
     */
    protected function _toRgb($hexStr, $returnAsString = false, $seperator = ','){
        return self::sToRgb($hexStr, $returnAsString, $seperator);
    }

    /**
     * @param $hexStr
     * @param bool $returnAsString
     * @param string $seperator
     * @return array|bool|string
     */
    public static function sToRgb($hexStr, $returnAsString = false, $seperator = ",") {
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

}
