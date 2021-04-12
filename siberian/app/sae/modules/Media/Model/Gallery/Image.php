<?php

/**
 * Class Media_Model_Gallery_Image
 *
 * @method Media_Model_Db_Table_Gallery_Image getTable()
 * @method setImageId(integer $imageId)
 * @method setName(string $name)
 * @method setTypeId(string $typeId)
 * @method integer getId()
 * @method string getTypeId()
 * @method integer getGalleryId()
 */
class Media_Model_Gallery_Image extends Core_Model_Default
{
    /**
     * @var bool
     */
    protected $_is_cacheable = true;

    /**
     * @var
     */
    protected $_type_instance;

    /**
     * @var array
     */
    protected $_types = [
        'picasa',
        'custom',
        'instagram',
        'flickr'
    ];

    /**
     * @var int
     */
    protected $_offset = 0;

    /**
     * @var string
     */
    protected $_db_table = Media_Model_Db_Table_Gallery_Image::class;

    /**
     * @param $valueId
     * @return array
     */
    public function getInappStates($valueId): array
    {
        return [
            [
                'state' => 'image-list',
                'offline' => true,
                'params' => [
                    'value_id' => $valueId,
                ],
            ],
        ];
    }

    /**
     * @param $option_value
     * @return array|boolean
     */
    public function getEmbedPayload($option_value = null)
    {

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
                ], ['position ASC', 'gallery_id ASC']);

            foreach ($galleries as $gallery) {
                $currentGallery = [
                    'id' => (integer)$gallery->getGalleryId(),
                    'name' => $gallery->getLabel() ? $gallery->getLabel() : $gallery->getName(),
                    'type' => $gallery->getTypeId(),
                ];

                $payload['galleries'][] = $currentGallery;
            }
        }

        return $payload;
    }

    /**
     * @param $id
     * @param null $field
     * @return $this
     */
    public function find($id, $field = null)
    {
        parent::find($id, $field);
        if ($this->getId()) {
            $this->_addTypeDatas();
        }

        return $this;
    }

    /**
     * @param array $values
     * @param null $order
     * @param array $params
     * @return mixed
     */
    public function findAll($values = [], $order = null, $params = [])
    {
        $rows = parent::findAll($values, $order, $params);
        foreach ($rows as $row) {
            $row->_addTypeDatas();
        }
        return $rows;
    }

    public function updatePosition ($galleryId, $position)
    {
        $this->getTable()->updatePosition ($galleryId, $position);
    }

    /**
     * @return Media_Model_Gallery_Image_Abstract
     */
    public function getTypeInstance()
    {
        if (!$this->_type_instance) {
            $type = $this->getTypeId();
            if (in_array($type, $this->_types)) {
                $class = 'Media_Model_Gallery_Image_' . ucfirst($type);
                $this->_type_instance = new $class();
                $this->_type_instance->addData($this->getData());
            }
        }

        return !empty($this->_type_instance) ? $this->_type_instance : null;

    }

    /**
     * @return $this
     */
    public function save()
    {
        $isDeleted = $this->getIsDeleted();
        parent::save();
        if (!$isDeleted && ($this->getTypeId() === 'picasa' || $this->getTypeId() === 'instagram')) {
            if ($this->getTypeInstance()->getId()) $this->getTypeInstance()->delete();
            $this->getTypeInstance()->setData($this->_getTypeInstanceData())->setGalleryId($this->getId())->save();
        }
        if (!$isDeleted && ($this->getTypeId() === 'flickr')) {
            $instance = new Media_Model_Gallery_Image_Flickr();
            $instance->find(['gallery_id' => $this->getTypeInstance()->getId()]);
            $instance->setData($this->_getTypeInstanceData())->setGalleryId($this->getId())->save();
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getAllTypes()
    {
        if ($this->getTypeInstance()) {
            return $this->getTypeInstance()->getAllTypes();
        }
        return [];
    }

    /**
     * @return array
     */
    public function getImages()
    {
        $results = [];
        if ($this->getId() && $this->getTypeInstance()) {
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

    /**
     * @return array
     */
    public function getAllImages()
    {
        if ($this->getId() && $this->getTypeInstance()) {
            return $this->getTypeInstance()->setImageId($this->getId())->getImages(null, null);
        }

        return [];
    }

    /**
     * @param $offset
     * @return $this
     */
    public function setOffset($offset)
    {
        $this->_offset = $offset;
        return $this;
    }

    /**
     * @deprecated
     * @param $option_value
     * @return array
     */
    public function getFeaturePaths($option_value)
    {
        return [];
    }

    /**
     * @deprecated
     * @param $option_value
     * @return array
     */
    public function getAssetsPaths($option_value)
    {
        return [];
    }

    /**
     * @deprecated
     * @param $option
     * @param null $parent_id
     * @return $this
     */
    public function copyTo($option, $parent_id = null)
    {
        return $this;
    }

    /**
     * @return $this
     */
    protected function _addTypeDatas()
    {
        if ($this->getTypeInstance()) {
            $this->getTypeInstance()->find($this->getId(), 'gallery_id');
            if ($this->getTypeInstance()->getId()) {
                $this->addData($this->getTypeInstance()->getData());
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    protected function _getTypeInstanceData()
    {
        $fields = $this->getTypeInstance()->getFields();
        $datas = [];
        foreach ($fields as $field) {
            $datas[$field] = $this->getData($field);
        }

        return $datas;
    }

    /**
     * @param $option_value
     * @param $design
     * @param $category
     */
    public function createDummyContents($option_value, $design, $category)
    {

        $dummy_content_xml = $this->_getDummyXml($design, $category);

        // Continue if dummy is empty!
        if (!$dummy_content_xml) {
            return;
        }

        if ($dummy_content_xml->images) {

            foreach ($dummy_content_xml->images->children() as $content) {

                $this->unsData();
                $this->addData((array)$content->content)
                    ->setValueId($option_value->getId())
                    ->save();

                if ($content->attributes()->type == "custom") {
                    foreach ($content->custom as $custom_images) {
                        $custom = new Media_Model_Gallery_Image_Custom();
                        $custom->setGalleryId($this->getId())
                            ->addData((array)$custom_images)
                            ->save();
                    }
                }
            }
        }
    }
}
