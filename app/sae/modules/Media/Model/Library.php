<?php

class Media_Model_Library extends Core_Model_Default {

    protected $_images;

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Media_Model_Db_Table_Library';
        return $this;
    }

    public function getImages() {

        if(empty($this->_images)) {
            $this->_images = array();
            $image = new Media_Model_Library_Image();
            if($this->getId()) {
                $this->_images = $image->findAll(array('library_id = ?' => $this->getId()), array('position ASC', 'image_id ASC', 'can_be_colorized DESC'));
            }
        }

        return $this->_images;

    }

    /**
     * @alias $this->getImages();
     */
    public function getIcons() {
        return $this->getImages();
    }

    public function copyTo($new_library_id,$option) {

        $images = $this->getImages();
        foreach($images as $image) {

            $file = pathinfo($image->getLink());
            $filename = $file['basename'];

            $relativePath = $option->getImagePathTo();
            $folder = Core_Model_Directory::getBasePathTo(Application_Model_Application::PATH_IMAGE.$relativePath);

            if(!is_dir($folder)) {
                mkdir($folder, 0777, true);
            }

            $img_src = Core_Model_Directory::getBasePathTo($image->getLink());
            $img_dst = $folder.'/'.$filename;

            if(copy($img_src, $img_dst)) {
                $image->setLink($relativePath.'/'.$filename);
            }

            $image->setId(null)
                ->setLibraryId($new_library_id)
                ->save();
        }

    }

}
