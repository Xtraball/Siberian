<?php

/**
 * Class Media_Model_Gallery_Image
 *
 * @method setImageId(integer $imageId)
 * @method setName(string $name)
 * @method setTypeId(string $typeId)
 * @method integer getId()
 * @method string getTypeId()
 * @method integer getGalleryId()
 */
class Media_Model_Gallery_Image extends Core_Model_Default {
    protected $_is_cacheable = true;

    protected $_type_instance;
    protected $_types = [
        'picasa',
        'custom',
        'instagram',
        'flickr',
        'facebook'
    ];
    protected $_offset = 0;

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Media_Model_Db_Table_Gallery_Image';
        return $this;
    }

    /**
     * @return array
     */
    public function getInappStates($value_id) {

        $in_app_states = array(
            array(
                "state" => "image-list",
                "offline" => true,
                "params" => array(
                    "value_id" => $value_id,
                ),
            ),
        );

        return $in_app_states;
    }

    /**
     * @param $option_value
     * @return array|boolean
     */
    public function getEmbedPayload($option_value) {

        $color = $this->getApplication()->getBlock('subheader')->getColor();
        $colorized_picto = Core_Controller_Default_Abstract::sGetColorizedImage(
            Core_Model_Lib_Image::sGetImage('pictos/more.png', true), $color);

        $payload = [
            'galleries' => [],
            'page_title' => $option_value->getTabbarName(),
            'header_right_button' => [
                'picto_url' => $colorized_picto
            ]
        ];

        if ($this->getId()) {
            $galleries = (new Media_Model_Gallery_Image())
                ->findAll([
                    'value_id' => $option_value->getId()
                ]);

            foreach($galleries as $gallery) {
                $currentGallery = [
                    'id' => (integer) $gallery->getGalleryId(),
                    'name' => $gallery->getLabel() ? $gallery->getLabel() : $gallery->getName(),
                    'type' => $gallery->getTypeId(),
                ];

                $payload['galleries'][] = $currentGallery;
            }
        }

        return $payload;
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

    /**
     * @return Media_Model_Gallery_Image_Abstract
     */
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
        if(!$isDeleted AND ($this->getTypeId() === 'picasa' || $this->getTypeId() === 'instagram')) {
            if($this->getTypeInstance()->getId()) $this->getTypeInstance()->delete();
            $this->getTypeInstance()->setData($this->_getTypeInstanceData())->setGalleryId($this->getId())->save();
        }
        if (!$isDeleted AND ($this->getTypeId() === 'flickr')) {
            $instance = new Media_Model_Gallery_Image_Flickr();
            $instance->find(array('gallery_id' => $this->getTypeInstance()->getId()));
            $instance->setData($this->_getTypeInstanceData())->setGalleryId($this->getId())->save();
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
        $results = [];
        if($this->getId() && $this->getTypeInstance()) {
            switch (get_class($this->getTypeInstance())) {
                case 'Media_Model_Gallery_Image_Facebook':
                    $results = $this->getTypeInstance()
                        ->setGalleryId($this->getGalleryId())
                        ->getImages($this->_offset);
                    break;
                default:
                    $results = $this->getTypeInstance()
                        ->setGalleryId($this->getGalleryId())
                        ->setImageId($this->getGalleryId())
                        ->getImages($this->_offset);
            }
        }

        return $results;
    }

    public function getAllImages() {
        if($this->getId() AND $this->getTypeInstance()) {
            return $this->getTypeInstance()->setImageId($this->getId())->getImages(null, null);
        }

        return array();
    }

    public function setOffset($offset) {
        $this->_offset = $offset;
        return $this;
    }

    public function getFeaturePaths($option_value) {

        if(!$this->isCacheable()) return array();

        $paths = array();
        $paths[] = $option_value->getPath("findall", array('value_id' => $option_value->getId()), false);

        $galleries = $this->findAll(array('value_id' => $option_value->getId()));
        foreach($galleries as $gallery) {

            if ($gallery->getId() && $gallery->getValueId() == $option_value->getId()) {

                $offset = 0;

                $more = true;
                while($more) {
                    $last_offset = $offset;

                    $paths[] = $option_value->getPath(
                        "media/mobile_gallery_image_view/find",
                        array(
                            "gallery_id" => $gallery->getId(),
                            "offset" => $offset,
                            "value_id" => $option_value->getId()
                        ),
                        false
                    );

                    $images = $gallery->setOffset($offset)->getImages();

                    // Stupid foreach to mimick controller and have same URLs as it
                    foreach ($images as $key => $link) {
                        $key+=$offset;
                        $last_offset = $link->getOffset();
                    }

                    if($gallery->getTypeId() != "custom") {
                        $more = count($data["images"]) > 0;
                    } else {
                        $more = ((($key - $offset) + 1) > (Media_Model_Gallery_Image_Abstract::DISPLAYED_PER_PAGE - 1)) ? true : false;
                    }

                    if($more) {
                        $offset = $last_offset + 1;
                    }
                }

            }
        }

        return $paths;
    }

    public function getAssetsPaths($option_value) {
        $assets = array();

        foreach($this->getAllImages() as $image) {
            $assets[] = $image->getImage();
        }

        return $assets;
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
        if ($this->getTypeInstance()) {
            $this->getTypeInstance()->find($this->getId(), 'gallery_id');
            if ($this->getTypeInstance()->getId()) {
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
