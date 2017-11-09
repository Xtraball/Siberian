<?php

use Gregwar\Cache\CacheInterface;

class Siberian_ZebraImage {

    /**
     * @var Zebra_Image
     */
    public $image = null;

    /**
     * @var null|string
     */
    public $cache_path = null;

    /**
     * 25ko max, otherwise it's an external resource
     *
     * @var int
     */
    public static $max_size = 25000;

    /**
     * Siberian_ZebraImage constructor.
     * @param null $originalFile
     */
    public function __construct($originalFile = null) {
        $this->cache_path = Core_Model_Directory::getImageCacheDirectory(true) . "/zebra";

        if(!file_exists($this->cache_path)) {
            mkdir($this->cache_path, 0777, true);
        }

        $this->image = new Zebra_Image();
        $this->image->source_path = $originalFile;

        return $this;
    }

    /**
     * @param int $width
     * @param int $height
     * @param int|string $method
     * @param string $background_color
     */
    public function resize($width = 0, $height = 0, $method = ZEBRA_IMAGE_CROP_CENTER,
                           $background_color = -1) {
        $this->image->preserve_aspect_ratio = true;
        $this->image->enlarge_smaller_images = true;

        $basename  = basename($this->image->source_path);
        $this->image->target_path = $this->cache_path . "/" . str_replace("." . pathinfo($this->image->source_path, PATHINFO_EXTENSION), "", $basename) . ".png";
        $this->image->resize($width, $height, $method, $background_color);

        return $this;
    }

    /**
     * @return string
     */
    public function inline() {
        $filetype = pathinfo($this->image->source_path, PATHINFO_EXTENSION);

        /// Seems 24-bit PNG are buggy and turn to solid black
        if($filetype === 'png') {
            return $this->image->source_path;
        }

        $content = file_get_contents($this->image->target_path);
        $base64 = base64_encode($content);

        if(strlen(base64_decode($content)) > self::$max_size) {
            $inline = $this->image->target_path;
        } else {
            $inline = "data:image/png;base64,{$base64}";
        }

        return $inline;
    }

}