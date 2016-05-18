<?php

class Media_Model_Gallery_Image extends Core_Model_Default {

    protected $_type_instance;
    protected $_types = array(
        'picasa', 'custom', 'instagram'
    );
    protected $_offset = 0;

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Media_Model_Db_Table_Gallery_Image';
        return $this;
    }

    public function find($id, $field = null) {
        parent::find($id, $field);
        if($this->getId()) {
            $this->_addTypeDatas();
        }

        return $this;
    }

    public function findAll($values = array(), $order = null, $params = array()) {
        $rows = parent::findAll($values, $order, $params);
        foreach($rows as $row) {
            $row->_addTypeDatas();
        }
        return $rows;
    }

    public function getTypeInstance() {
        if(!$this->_type_instance) {
            $type = $this->getTypeId();
            if(in_array($type, $this->_types)) {
                $class = 'Media_Model_Gallery_Image_'.ucfirst($type);
                $this->_type_instance = new $class();
                $this->_type_instance->addData($this->getData());
            }
        }

        return !empty($this->_type_instance) ? $this->_type_instance : null;

    }

    public function save() {
        $isDeleted = $this->getIsDeleted();
        parent::save();
        if(!$isDeleted AND ($this->getTypeId() == 'picasa' || $this->getTypeId() == 'instagram')) {
            if($this->getTypeInstance()->getId()) $this->getTypeInstance()->delete();
            $this->getTypeInstance()->setData($this->_getTypeInstanceData())->setGalleryId($this->getId())->save();
        }
        return $this;
    }

    public function getAllTypes() {
        if($this->getTypeInstance()) {
            return $this->getTypeInstance()->getAllTypes();
        }
        return array();
    }

    public function getImages() {
        if($this->getId() AND $this->getTypeInstance()) {
            return $this->getTypeInstance()->setImageId($this->getId())->getImages($this->_offset);
        }
        
        return array();
    }

    public function setOffset($offset) {
        $this->_offset = $offset;
        return $this;
    }

    public function getFeaturePaths($option_value) {

        if(!$this->isCachable()) return array();

        $action_view = $this->getActionView();

        $paths = array();
        $paths[] = $option_value->getPath("findall", array('value_id' => $option_value->getId()), false);

        if($uri = $option_value->getMobileViewUri($action_view)) {

            $uri_parameters = $option_value->getMobileViewUriParameter();

            if ($uri_parameters) {
                $uri_parameters = "value_id,$uri_parameters";
                $uri_parameters = explode(",", $uri_parameters);

                foreach ($uri_parameters as $uri_parameter) {
                    if (stripos($uri_parameter, "/") !== false) {
                        $data = explode("/", $uri_parameter);
                        $params[$data[0]] = $data[1];
                    } else if ($data = $this->getData($uri_parameter)) {
                        $params[$uri_parameter] = $data;
                    }
                }
            }

            $paths[] = $option_value->getPath($uri, $params, false);

            $images = $this->getImages();

            foreach($images as $image) {
                $paths[] = $image->getImage();
            }

        }

        return $paths;

    }

    public function copyTo($option) {

        $images = array();
        if($this->getTypeId() == 'custom') {
            $image = new Media_Model_Gallery_Image_Custom();
            $images = $image->findAll(array('gallery_id' => $this->getId()));
        }

        $this->getTypeInstance()->setId(null);
        $this->setId(null)
            ->setValueId($option->getId())
            ->save()
        ;

        foreach($images as $image) {

            if(file_exists(BASE_PATH.Application_Model_Application::PATH_IMAGE.$image->getData('url'))) {

                $image_url = Application_Model_Application::PATH_IMAGE.$image->getData('url');
                $file = pathinfo($image_url);
                $filename = $file['basename'];

                $relativePath = $option->getImagePathTo();
                $folder = Core_Model_Directory::getBasePathTo(Application_Model_Application::PATH_IMAGE.'/'.$relativePath);

                if(!is_dir($folder)) {
                    mkdir($folder, 0777, true);
                }

                $img_src = Core_Model_Directory::getBasePathTo($image_url);
                $img_dst = $folder.'/'.$filename;

                if(copy($img_src, $img_dst)) {
                    $image->setId(null)
                        ->setGalleryId($this->getId())
                        ->setData('url', $relativePath.'/'.$filename)
                        ->save()
                    ;
                }

            }
        }

        return $this;

    }

    protected function _addTypeDatas() {
        if($this->getTypeInstance()) {
            $this->getTypeInstance()->find($this->getId());
            if($this->getTypeInstance()->getId()) {
                $this->addData($this->getTypeInstance()->getData());
            }
        }

        return $this;
    }

    protected function _getTypeInstanceData() {
        $fields = $this->getTypeInstance()->getFields();
        $datas = array();
        foreach($fields as $field) {
            $datas[$field] = $this->getData($field);
        }

        return $datas;
    }

    public function createDummyContents($option_value, $design, $category) {

        $dummy_content_xml = $this->_getDummyXml($design, $category);

        if($dummy_content_xml->images) {

            foreach ($dummy_content_xml->images->children() as $content) {

                $this->unsData();
                $this->addData((array)$content->content)
                    ->setValueId($option_value->getId())
                    ->save();

                if ($content->attributes()->type == "custom") {
                    foreach($content->custom as $custom_images) {
                        $custom = new Media_Model_Gallery_Image_Custom();
                        $custom->setGalleryId($this->getId())
                            ->addData((array) $custom_images)
                            ->save();
                    }
                }
            }
        }
    }
}
