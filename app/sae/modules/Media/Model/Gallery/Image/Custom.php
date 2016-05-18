<?php

class Media_Model_Gallery_Image_Custom extends Media_Model_Gallery_Image_Abstract {

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Media_Model_Db_Table_Gallery_Image_Custom';
        return $this;
    }

    public function getAllTypes() {
        $types = array();
        foreach($this->_flux as $k => $flux) {
            $types[$k] = new Core_Model_Default(array(
                'code' => $k,
                'url' => $flux,
                'label' => $this->_labels[$k]
            ));
        }
        return $types;
    }

    public function getImages($offset) {

        if(!$this->_images) {

            $this->_images = array();

            try {
                $image = new Media_Model_Gallery_Image_Custom();
                $params = array('limit' => self::DISPLAYED_PER_PAGE);
                if(!empty($offset)) $params['offset'] = $offset;

                $images = $image->findAll(array('gallery_id' => $this->getImageId()), 'image_id DESC', $params);
            }
            catch(Exception $e) {
                $images = array();
            }

            foreach ($images as $key => $image) {
                $this->_images[] = new Core_Model_Default(array(
                    'offset'  => $offset++,
                    'title'  => $image->getTitle(),
                    'description'  => $image->getDescription(),
                    'author' => null,
                    'image'  => Application_Model_Application::getImagePath().$image->getData('url')
                ));
            }

        }

        return $this->_images;
    }

}

