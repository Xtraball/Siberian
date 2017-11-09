<?php

class Thumbnailer_CreateThumb {

    /**
     * Create thumbnail of an image
     * @param     string    Path of source image
     * @param     string    Destination path of thumbnail
     * @param     number    Width of thumbnail
     * @param     number    Height of thumbnail
     * @param     number    (optional) thumbnail image format : JPG, GIF, PNG
     */
    public static function createThumbnail($sourcePath, $destPath, $width, $height, $q = 'JPG', $crop = false, $options = array()) {

        try {
            $destDir = dirname($destPath);
            if(!is_dir($destDir)) { mkdir($destDir, 0777); }

            list($max_width, $max_height) = getimagesize($sourcePath);
            $ratio = $max_width/$max_height;

            if ($ratio<1) $height = intval($height/$ratio);
            else $width = intval($width*$ratio);

            $options['correctPermissions'] = true;
            $thumb = Thumbnailer_ThumbLib::create($sourcePath, $options);
    //        if(!empty($crop)) $thumb->crop($crop['x'], $crop['y'], $w, $h);
            $thumb->resize($width, $height);

            if($crop) {
                $thumb->cropFromCenter($width, $height);
            }

            $thumb->save($destPath, $q);
        }
        catch(Exception $e) {
            $destPath = $e->getMessage();
        }
        return $destPath;
    }

    public static function resize($sourcePath, $destPath, $width, $height, $q = 'JPG', $crop = false) {

        try {
            $destDir = dirname($destPath);
            if(!is_dir($destDir)) { mkdir($destDir, 0777); }

            list($max_width, $max_height) = getimagesize($sourcePath);

            $options = array('correctPermissions' => true, 'resizeUp' => true);
            $thumb = Thumbnailer_ThumbLib::create($sourcePath, $options);

            $width_ratio = $max_width / $width;
            $height_ratio = $max_height / $height;

            if($max_width < $width OR $max_width < $height)
            {
                if($width_ratio > $height_ratio) {
                    $thumb->resize(0, $height);
                } else {
                    $thumb->resize($width, 0);
                }
                if($crop) {
                    $thumb->crop(0, 0, $width, $height);
                }
            } else {
                $thumb->resize($width, $height);
            }

            $thumb->save($destPath, $q);
        }
        catch(Exception $e) {
            $destPath = $e->getMessage();
        }
        return $destPath;
    }

    public static function crop($sourcePath, $destPath, $x, $y, $width, $height, $fromCenter = false) {

        try {

            if(empty($destPath)) $destPath = $sourcePath;
            $destDir = dirname($destPath);

            if(!is_dir($destDir)) { mkdir($destDir, 0777); }

            // Crop l'image
            $thumb = Thumbnailer_ThumbLib::create($sourcePath, array('correctPermissions' => true));
            if($fromCenter) $thumb->cropFromCenter($width, $height);
            else $thumb->crop($x, $y, $width, $height);
            $thumb->save($destPath);

        }
        catch(Exception $e) {
            $destPath = null;
        }
        return $destPath;

    }

}