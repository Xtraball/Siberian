<?php

class Application_Model_Option_Value extends Application_Model_Option
{

    protected static $_editor_icon_color = "#FFFFFF";

    protected $_background_image_url;

    public function __construct($datas = array()) {
        parent::__construct($datas);
        $this->_db_table = 'Application_Model_Db_Table_Option_Value';
    }

    public function find($id, $field = null) {
        parent::find($id, $field);
        $this->addOptionDatas();
        $this->prepareUri();
        return $this;
    }

    public function findFolderValues($app_id, $option_id) {
        $folderValues = $this->getTable()->getFolderValues($app_id, $option_id);
        return $folderValues;
    }

    public function getDummy() {
        $color = str_replace('#', '', $this->getApplication()->getBlock('tabbar')->getImageColor());
        $option = new Application_Model_Option();
        $option->find('newswall', 'code');
        $dummy = new self();

        $dummy->addData($option->getData())
            ->setTabbarName('Sample')
            ->setIsDummy(1)
            ->setIsActive(1)
            ->setIconUrl(Core_Model_Url::create("template/block/colorize", array('id' => $dummy->getIconId(), 'color' => $color)))
            ->setId(0)
        ;

        return $dummy;
    }

    public function save() {

        if(!$this->getId()) {
            $this->setNextPosition();
            $this->setLayoutId(1)->setIsActive(1);
        }
        return parent::save();

    }

    public function delete() {
//        if($this->getObject()->getId()) {
        $this->getObject()->deleteFeature($this->getOptionId());
//        }
        parent::delete();
    }

    public function getIconColor() {
        return self::$_editor_icon_color;
    }

    public function isActive() {
        return $this->getIsActive();
    }

    public function getImagePathTo($folder = "") {
        $path = '/'.$this->getAppId().'/features/'.$this->getCode().'/'.$this->getId();
        if(!empty($folder)) {
            $path .= '/'.$folder;
        }
        return $path;
    }

    public function getLibrary() {
        if(!$this->getLibraryId()) {
            $this->_findLibraryId();
        }
        return parent::getLibrary();
    }

    public function getLibraryId() {
        if(!$this->getData('library_id')) {
            $this->_findLibraryId();
        }
        return $this->getData('library_id');
    }

    public function getBackgroundImageUrl() {

        if(!$this->_background_image_url) {

            if($this->getBackgroundImage() AND $this->getBackgroundImage() != "no-image") {
                $this->_background_image_url = Application_Model_Application::getImagePath().$this->getBackgroundImage();
            }

        }

        return $this->_background_image_url;

    }

    public function addOptionDatas() {
        if(is_numeric($this->getId())) {
            $datas = $this->getTable()->getOptionDatas($this->getOptionId());
            foreach($datas as $key => $value) {
                if(is_null($this->getData($key))) $this->setData($key, $value);
            }

        }
        return $this;
    }

    public static function setEditorIconColor($icon_color) {
        self::$_editor_icon_color = $icon_color;
    }

    protected function _findLibraryId() {

        $library_id = $this->getTable()->findLibraryId($this->getOptionId());
        $this->setLibraryId($library_id);

        return $this;
    }

    public function copyTo($application) {

        $old_value_id = $this->getId();
        $old_app_id = $this->getAppId();
        $object = $this->getObject();

        $this->setId(null)
//            ->setApplication($application)
            ->setAppId($application->getId())
            ->save()
        ;

        $this->setOldValueId($old_value_id)
            ->setOldAppId($old_app_id)
        ;

        if($object->getId()) {
            $collection = $object->findAll(array('value_id' => $this->getOldValueId()));
            foreach($collection as $object) {
                $object->copyTo($this);
            }
        }

        return $this;
    }

    protected function setNextPosition() {

        $lastPosition = $this->getTable()->getLastPosition($this->getApplication()->getId());
        if(!$lastPosition) $lastPosition = 0;
        $this->setPosition(++$lastPosition);

        return $this;
    }

    public function getNextFolderCategoryPosition($category_id) {
        $lastPosition = $this->getTable()->getLastFolderCategoryPosition($category_id);
        if(!$lastPosition) $lastPosition = 0;

        return ++$lastPosition;
    }

    public function isLocked() {
        return $this->getData('is_locked');
    }
    
}
