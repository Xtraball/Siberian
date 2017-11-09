<?php

class Media_Model_Gallery_Video extends Core_Model_Default {

    protected $_type_instance;
    protected $_types = array(
        'youtube',
        'itunes',
        'vimeo'
    );
    protected $_offset = 1;

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Media_Model_Db_Table_Gallery_Video';
        return $this;
    }

    /**
     * @return array
     */
    public function getInappStates($value_id) {

        $in_app_states = array(
            array(
                "state" => "video-list",
                "offline" => false,
                "params" => array(
                    "value_id" => $value_id,
                ),
            ),
        );

        return $in_app_states;
    }

    /**
     * @param $option_value
     * @return bool
     */
    public function getEmbedPayload($option_value) {

        $payload = array(
            "collection"                => array(),
            "page_title"                => $option_value->getTabbarName(),
            "displayed_per_page"        => Media_Model_Gallery_Video_Abstract::DISPLAYED_PER_PAGE
        );

        $video = new Media_Model_Gallery_Video();
        $videos = $video->findAll(array("value_id" => $option_value->getId()));
        $has_youtube_videos = false;

        foreach($videos as $video) {
            $payload["collection"][] = array(
                "id"                => (integer) $video->getId(),
                "name"              => $video->getName(),
                "type"              => $video->getTypeId(),
                "search_by"         => $video->getType(),
                "search_keyword"    => $video->getParam()
            );

            if($video->getTypeId() == "youtube") {
                $has_youtube_videos = true;
            }
        }

        if($has_youtube_videos) {
            $payload["youtube_key"] = Api_Model_Key::findKeysFor('youtube')->getApiKey();
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

    public function getTypeInstance() {

        if(!$this->_type_instance) {
            $type = $this->getTypeId();
            if(in_array($type, $this->_types)) {
                $class = 'Media_Model_Gallery_Video_'.ucfirst($type);
                $this->_type_instance = new $class();
                $this->_type_instance->addData($this->getData());
            }
        }

        return !empty($this->_type_instance) ? $this->_type_instance : null;

    }

    public function save() {
        $isDeleted = $this->getIsDeleted();
        parent::save();
        if(!$isDeleted) {
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

    public function getVideos() {
        if($this->getId() AND $this->getTypeInstance()) {
            return $this->getTypeInstance()->getVideos($this->_offset);
        }
        return array();
    }

    public function setOffset($offset) {
        $this->_offset = $offset;
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

        if($dummy_content_xml->videos) {

            foreach ($dummy_content_xml->videos->children() as $content) {

                $this->unsData();
                $this->_type_instance = null;
                $this->addData((array) $content->content)
                    ->setValueId($option_value->getId())
                    ->save()
                ;
            }
        }
    }

    public function copyTo($option) {

        $this->getTypeInstance()->setId(null);
        $this->setId(null)
            ->setValueId($option->getId())
            ->save()
        ;

        return $this;
    }
}
