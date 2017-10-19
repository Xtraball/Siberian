<?php

class Siberian_Image extends Gregwar\Image\Image {

    /**
     * @var array
     */
    public static $formats = [
        'thumbnail' => 256
    ];

    /**
     * @var null
     */
    public $originalFile = null;

    /**
     * 25ko max, otherwise it's an external resource
     *
     * @var int
     */
    public static $max_size = 25000;

    /**
     * @var bool
     */
    protected static $force_cache = true;

    /**
     * Siberian_Image constructor.
     * @param null $originalFile
     * @param null $width
     * @param null $height
     */
    public function __construct($originalFile = null,
                                $width = null, $height = null) {
        parent::__construct($originalFile, $width, $height);

        $this->originalFile = $originalFile;

        $this->setCacheDir(Core_Model_Directory::getImageCacheDirectory(true));
        $this->setCacheDirMode(0755);
    }

    /**
     * Returns the Base64 inlinable representation.
     */
    public function inline($type = 'jpg', $quality = 80) {
        $extension = pathinfo($this->source->getFile(), PATHINFO_EXTENSION);
        if($extension === 'png') {
            return 'data:image/png;base64,' .
                base64_encode(file_get_contents($this->originalFile));
        }

        return parent::inline($type, $quality);
    }

    /**
     * @param $base_url
     * @param $resource
     * @param null $format
     * @param null $device_width
     * @param null $device_height
     * @return mixed|string
     * @throws Siberian_Exception
     */
    public static function getForMobile($base_url,
                                        $resource,
                                        $format = null,
                                        $device_width = null,
                                        $device_height = null,
                                        $returnInfos = false) {
        if(isset($resource) && is_file($resource)) {

            $resource = Siberian_Image::open($resource);

            // Optimize images with the screen resolution or format!
            if(isset($format) && isset(self::$formats[$format])) {
                $max_width = self::$formats[$format];
            } else if(isset($device_width) && isset($device_height)) {
                $max_width = ($device_width*1 > $device_height*1) ?
                    $device_width*1 : $device_height*1;
            } else {
                $max_width = 1024;
            }

            // Range sizes, this will group cache, to reduce the spread
            if (($max_width < 512) && ($max_width >= 256)) {
                $max_width = 256;
            } else if (($max_width < 720) && ($max_width >= 512)) {
                $max_width = 512;
            } else if (($max_width < 1024) && ($max_width >= 720)) {
                $max_width = 720;
            }

            // Resize only if image width is bigger than guessed range!
            if ($resource->width() > $max_width) {
                $resource = $resource->cropResize($max_width, null, 'transparent');
            }

            $base64 = $resource->inline($resource->guessType());

            /**
             *  If the image is bigger than 100kb,
             *  don't cache it locally but send the proxied url
             */
            if(strlen(base64_decode($base64)) > self::$max_size || self::$force_cache) {
                $data = str_replace(Core_Model_Directory::getBasePathTo(''),
                    $base_url . '/', $resource->guess());
            } else {
                $data = $base64;
            }

            if (!$returnInfos) {
                return $data;
            }

            return [
                'type' => $resource->guessType(),
                'data' => $data
            ];

        } else {
            throw new Siberian_Exception(
                __('[Error] Siberian_Image, no resource provided.'));
        }
    }

    /**
     * Force image to be cached as URL
     */
    public static function enableForceCache () {
        self::$force_cache = true;
    }

    /**
     * Disable image URL cache
     */
    public static function disableForceCache () {
        self::$force_cache = false;
    }

}