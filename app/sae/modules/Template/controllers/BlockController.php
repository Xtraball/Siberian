<?php

class Template_BlockController extends Core_Controller_Default {

    public function blankimageAction() {

        $image = imagecreatetruecolor($this->getRequest()->getParam('width', 320), $this->getRequest()->getParam('height', 75));
        $color = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $color);
        header('Content-type: image/png');
        imagepng($image);
        imagedestroy($image);

        die;

    }

    public function colorizeAction() {

        Siberian_Media::disableTemporary();

        if(($this->getRequest()->getParam('id') || $this->getRequest()->getParam('url') || $this->getRequest()->getParam('path')) AND $color = $this->getRequest()->getParam('color')) {

            $params = array('id', 'url', 'path', 'color');
            $path = '';
            foreach($params as $param) $id[] = $this->getRequest()->getParam($param);
            $id = md5(implode('+', $id));

            if($image_id = $this->getRequest()->getParam('id')) {
                $image = new Media_Model_Library_Image();
                $image->find($image_id);
                if(!$image->getCanBeColorized()) $color = null;
                $path = $image->getLink();
                $path = Media_Model_Library_Image::getBaseImagePathTo($path, $image->getAppId());
            } else if($url = $this->getRequest()->getParam('url')) {
                $path = Core_Model_Directory::getTmpDirectory(true).'/'.$url;
            } else if($path = $this->getRequest()->getParam('path')) {
                $path = base64_decode($path);
                if(!Zend_Uri::check($path)) {
                    $path = Core_Model_Directory::getBasePathTo($path);
                    if(!is_file($path)) die;
                }
            }

            $image = new Core_Model_Lib_Image();
            $image->setId($id)
                ->setPath($path)
                ->setColor($color)
                ->colorize()
            ;

            ob_start();
            imagepng($image->getResources());
            $contents = ob_get_contents();
            ob_end_clean();
            imagedestroy($image->getResources());

            $this->getResponse()->setHeader('Content-Type', 'image/png');
            $this->getLayout()->setHtml($contents);

        }

    }
}
