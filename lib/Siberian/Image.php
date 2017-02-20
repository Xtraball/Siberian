<?php

class Siberian_Image extends Gregwar\Image\Image {

    /**
     * Siberian_Image constructor.
     * @param null $originalFile
     * @param null $width
     * @param null $height
     */
    public function __construct($originalFile = null, $width = null, $height = null) {
        parent::__construct($originalFile, $width, $height);

        $this->setCacheDir(Core_Model_Directory::getImageCacheDirectory(true));
        $this->setCacheDirMode(0755);
    }

}