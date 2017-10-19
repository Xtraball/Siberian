<?php

/**
 * Class Application_Model_Option_Value
 *
 * This object is a copy of Application_Model_Option
 * used to save per-app options/features
 *
 * @method integer getId()
 *
 */
class Application_Model_Option_Value extends Application_Model_Option
{

    protected $_design_colors = array(
        "angular" => "#FFFFFF", // Fallback value
        "flat" => "#0099C7",
        "siberian" => "#FFFFFF"
    );
    protected $_metadata;
    protected static $_editor_icon_color = null;
    protected static $_editor_icon_reverse_color = null;

    protected $_background_image_url;
    protected $_background_landscape_image_url;

    /**
     * @param bool $base
     * @return string
     */
    public function getIconUrl($base = false) {
        if(empty($this->_icon_url) AND $this->getIconId()) {
            if($this->getIcon() AND !$base) {
                $this->_icon_url = Media_Model_Library_Image::getImagePathTo($this->getIcon(), $this->getAppId());
            }
            else {
                $icon = new Media_Model_Library_Image();
                $icon->find($this->getIconId());
                $this->_icon_url = $icon->getUrl();
            }
        }

        return $this->_icon_url;
    }

    public function __construct($datas = array()) {
        parent::__construct($datas);
        $this->_db_table = 'Application_Model_Db_Table_Option_Value';

        if(!self::$_editor_icon_color) {
            if (array_key_exists(DESIGN_CODE, $this->_design_colors)) {
                self::$_editor_icon_color = $this->_design_colors[DESIGN_CODE];
            }
        }

        if(!self::$_editor_icon_reverse_color) {
            self::$_editor_icon_reverse_color = "#ffffff";
        }
    }

    public function find($id, $field = null) {
        parent::find($id, $field);

        $this->addOptionDatas();
        $this->prepareUri();

        return $this;
    }

    /**
     * @param $value_id
     * @param $app_id
     * @return bool
     */
    public function valueIdBelongsTo($value_id, $app_id) {
        return $this->getTable()->valueIdBelongsTo($value_id, $app_id);
    }

    public function findAllWithOptionsInfos($values = array(), $order = null, $params = array()) {
        return $this->getTable()->findAllWithOptionsInfos($values, $order, $params);
    }

    public function getFeaturesByApplication() {
        return $this->getTable()->getFeaturesByApplication();
    }

    public function findFolderValues($app_id, $option_id) {
        $folderValues = $this->getTable()->getFolderValues($app_id, $option_id);
        return $folderValues;
    }

    public function getDummy() {
        if((isset($this) && get_class($this) == __CLASS__)) {
            $this->getApplication();

            $color = str_replace('#', '', $this->getApplication()->getBlock('tabbar')->getImageColor());
            $option = new Application_Model_Option();
            $option->find('newswall', 'code');
            $dummy = new self();

            $dummy->addData($option->getData())
                ->setTabbarName(__("Sample"))
                ->setIsDummy(1)
                ->setIsActive(1)
                ->setIconUrl(Core_Model_Url::create("template/block/colorize", array('id' => $dummy->getIconId(), 'color' => $color)))
                ->setId(0)
            ;

        } else {
            $option = new Application_Model_Option();
            $option->find('newswall', 'code');
            $dummy = new self();
            $dummy->addData($option->getData())
                ->setTabbarName(__("Sample"))
                ->setIsDummy(1)
                ->setIsActive(1)
                ->setIconUrl(Core_Model_Url::create("template/block/colorize", array('id' => $dummy->getIconId(), 'color' => '000000')))
                ->setId(0)
            ;
        }

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
        $this->getObject()->deleteFeature($this->getOptionId());

        parent::delete();
    }

    public function getIconColor() {
        return self::$_editor_icon_color;
    }

    public function getIconReverseColor() {
        return self::$_editor_icon_reverse_color;
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

    public function getBackgroundImageUrl() {
        if (!$this->_background_image_url) {
            if ($this->getBackgroundImage() AND $this->getBackgroundImage() != 'no-image') {
                $this->_background_image_url = Application_Model_Application::getImagePath() . $this->getBackgroundImage();
            }
        }

        return $this->_background_image_url;
    }

    public function getBackgroundLandscapeImageUrl() {
        if (!$this->_background_landscape_image_url) {
            if ($this->getBackgroundLandscapeImage() AND $this->getBackgroundLandscapeImage() != 'no-image') {
                $this->_background_landscape_image_url = Application_Model_Application::getImagePath() . $this->getBackgroundLandscapeImage();
            }
        }

        return $this->_background_landscape_image_url;
    }

    public function addOptionDatas() {
        if (is_numeric($this->getId())) {
            $datas = $this->getTable()->getOptionDatas($this->getOptionId());
            foreach ($datas as $key => $value) {
                if (is_null($this->getData($key))) $this->setData($key, $value);
            }
        }

        return $this;
    }

    public static function setEditorIconColor($icon_color) {
        self::$_editor_icon_color = $icon_color;
    }

    public static function setEditorIconReverseColor($icon_color) {
        self::$_editor_icon_reverse_color = $icon_color;
    }


    public function copyTo($application) {

        $old_value_id = $this->getId();
        $old_app_id = $this->getAppId();
        $object = $this->getObject();

        $this->setId(null)
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

    /**
     * Get a string array of tags names associated with the given domain object (e.g. Pages).
     * Tags Can be associated to any domain object, domain objects are always associated with features.
     *
     * Usage example:
     * $option_value = new Application_Model_Option_Value();
     * $option_value->find($value_id);
     * $page = new Cms_Model_Application_Page();
     * $page->find($page_id);
     * $tag_names = $option_value->getTagNames($page);
     *
     * @param  Object $object
     * @return array An array of strings
     */
    public function getTagNames($object)
    {
        $tag_names = array();
        foreach ($this->getOwnTags($object) as $tag) {
            $tag_names[] = trim($tag->getName());
        }
        $tag_names = array_unique($tag_names);

        return $tag_names;
    }

    /**
     * Get the tags associated with the given domain object (e.g. Pages).
     * Tags Can be associated to any domain object, domain objects are always associated with features.
     *
     * Usage example:
     * $option_value = new Application_Model_Option_Value();
     * $option_value->find($value_id);
     * $page = new Cms_Model_Application_Page();
     * $page->find($page_id);
     * $tags = $option_value->getOwnTags($page);
     *
     * @param  Object $object
     * @return array An array of Application_Model_Db_Table_Tag
     */
    public function getOwnTags($object)
    {
        return self::getTags($this->getValueId(), $object);
    }

    /**
     * Get the tags associated with the given domain object belonging to a feature.
     * Tags Can be associated to any domain object, domain objects are always associated with features.
     *
     * Usage example:
     * $page = new Cms_Model_Application_Page();
     * $page->find($page_id);
     * $tags = Application_Model_Option_Value::getTags($value_id, $page);
     *
     * @param  int $value_id
     * @param  Object $object
     * @return array             An array of Application_Model_Db_Table_Tag
     */
    public static function getTags($value_id, $object)
    {
        $model_name = method_exists($object, "getModelClass") ? $object->getModelClass() : get_class($object);

        $table = new Application_Model_Db_Table_Tag();
        $select = $table->select(Zend_Db_Table::SELECT_WITH_FROM_PART);
        $select
            ->setIntegrityCheck(false)
            ->join('application_tagoption', 'application_tagoption.tag_id = application_tag.tag_id')
            ->where('application_tagoption.value_id = ?', $value_id)
            ->where('application_tagoption.model = ?', $model_name);

        // If the object has no id then retrieve all the tags associated with Objects of the same type belonging to the feature
        $zend_validate_int = new Zend_Validate_Int();
        if ($zend_validate_int->isValid($object->getId())) {
            $select->where('application_tagoption.object_id = ?', $object->getId());
        }

        $rows = $table->fetchAll($select);
        return $rows;
    }

    /**
     * Associates an array of tags to a domain object.
     * Removes the old tags associations and replaces them with the new ones.
     *
     * Usage example:
     * $option_value = new Application_Model_Option_Value();
     * $option_value->find($value_id);
     * $page = new Cms_Model_Application_Page();
     * $page->find($page_id);
     * $tags = Application_Model_Tag::upsert($tag_names);
     * $option_value->attachTags($tags, $page);
     *
     * @param  array $tags An array of Application_Model_Db_Table_Tag
     * @param  Object $object
     * @return $this
     */
    public function attachTags($tags, $object)
    {
        return $this->flushTags($object)->_addTags($tags, $object);
    }

    /**
     * Associates an array of tags to a domain object.
     * May duplicate already made tag associations. Best used with flushTags.
     *
     * @param  array $tags An array of Application_Model_Db_Table_Tag
     * @param  Object $object
     * @return $this
     */
    protected function _addTags($tags, $object)
    {
        foreach ($tags as $tag) {
            $new_tag = new Application_Model_TagOption();
            $new_tag->setTagId($tag->getTagId())
                ->setObject($object)
                ->save();
        }
        return $this;
    }

    /**
     * Removes all tag associations of an object.
     *
     * Usage example:
     * $option_value = new Application_Model_Option_Value();
     * $option_value->find($value_id);
     * $page = new Cms_Model_Application_Page();
     * $page->find($page_id);
     * $option_value->flushTags($page);
     *
     * @param  Object $object
     * @return $this
     */
    public function flushTags($object)
    {
        $table = new Application_Model_Db_Table_TagOption();
        $model_name = method_exists($object, "getModelClass") ? $object->getModelClass() : get_class($object);
        $where = array(
            $table->getAdapter()->quoteInto('value_id = ?', $this->getValueId()),
            $table->getAdapter()->quoteInto('object_id = ?', $object->getId()),
            $table->getAdapter()->quoteInto('model = ?', $model_name)
        );
        $table->delete($where);
        return $this;
    }

    /**
     * Returns all the metadata associated with the current Feature
     *
     * @return collection           A collection of Application_Model_Db_Table_Option_Value_Metadata
     */
    public function getMetadatas()
    {
        $matadata = new Application_Model_Option_Value_Metadata();
        $results = $matadata->findAll(array('value_id' => $this->getValueId()));
        $this->_metadata = array();
        foreach ($results as $result) {
            $this->_metadata[$result->getCode()] = $result;
        }
        return $this->_metadata;
    }

    /**
     * Returns the metadatum which has the code specified.
     *
     * @param string                e.g. 'search_by_text'
     * @return collection           A collection of Cms_Model_Db_Table_Application_Page_Metadata
     */
    public function getMetadata($code)
    {
        $metadata = new Application_Model_Option_Value_Metadata();
        $metadata->find(array('value_id' => $this->getValueId(), 'code' => $code));
        return $metadata;
    }

    /**
     * Sets the feature metadata to the data provided.
     * Assumes all the keys absent to be removed.
     *
     * @param array                 An array of key, value tuples
     * @return $this
     */
    public function setMetadata($data)
    {
        foreach ($this->getMetadatas() as $metadatum) {
            // If there already are metadata with the given name update them
            if (array_key_exists($metadatum->getCode(), $data)) {
                $metadatum->setPayload($data[$metadatum->getCode()]);
                unset($data[$metadatum->getCode()]);
            } else {
                $metadatum->setPayload(null);
            }
        }
        // Otherwize create a new metadata and add it to the current metadata
        foreach ($data as $code => $payload) {
            array_push($this->_metadata, $this->_createMetadata($code, $payload));
        }
        return $this;
    }

    /**
     * Gets the string value of the metadatum specified by the $code
     *
     * @param string                 Metadatum name
     * @return string
     */
    public function getMetadataValue($code)
    {
        $meta = $this->getMetadata($code);
        if ($meta) {
            return $meta->getPayload();
        } else {
            return null;
        }
    }

    /**
     * Save the metadata defined on the current page
     *
     * @return $this
     */
    public function saveMetadata()
    {
        foreach ($this->_metadata as $metadatum) {
            $metadatum->save();
        }
        return $this;
    }


    /**
     * @param bool $base64
     * @return string
     */
    public function _getBackground() {
        return $this->__getBase64Image($this->getBackground());
    }

    /**
     * @param $base64
     * @param $option
     * @return $this
     */
    public function _setBackground($base64, $option) {
        $background_path = $this->__setImageFromBase64($base64, $option, 1080, 1920);
        $this->setBackground($background_path);

        return $this;
    }

    /**
     * Creates an instance of Cms_Model_Application_Page_Metadata and configures it with the current feature
     *
     * @return $metadata     An instance of Cms_Model_Application_Page_Metadata
     */
    protected function _createMetadata($code, $payload)
    {
        $meta = new Application_Model_Option_Value_Metadata();
        $meta->setValueId($this->getValueId());
        $meta->setCode($code);
        $meta->setPayload($payload);
        return $meta;
    }

    /**
     * @param $path
     * @throws Exception
     */
    public function readOption($path) {
        $content = file_get_contents($path);

        try {
            $dataset = Siberian_Yaml::decode($content);
        } catch(Exception $e) {
            throw new Exception("#043-01: An error occured while importing YAML dataset '$path'.");
        }

        if(isset($dataset["option"])) {
            return $this->setData($dataset["option"]);
        } else {
            throw new Exception("#087-02: Missing option, unable to import data.");
        }
    }

    /**
     * Prepare the export for YAML (with base64 background)
     *
     * @return array|mixed|null|string
     */
    public function forYaml() {
        $data = $this->getData();

        if($this->getBackground()) {
            $data["background"] = $this->_getBackground();
        }

        return $data;
    }

    /**
     * Touch option_value.
     *
     * @context progressive web apps, cache rules
     *
     * @return Application_Model_Option_Value
     */
    public function touch() {
        $this->setTouchedAt(time())->save();

        # Refresh corresponding cache
        $tag = "homepage_app_".$this->getApplication()->getId();
        $this->cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, array($tag));

        return $this;
    }

    /**
     * When expires_at is set to null, the cache will never expire until a manual trigger is done, via push or via touch.
     * If touch is newer than the saved touch, then it will be refreshed.
     * If expires is past, then even if option is un-touched, cache will be refreshed.
     *
     * @context progressive web apps, cache rules
     *
     * @param null|int $time -1 will never expire.
     * @return Application_Model_Option_Value
     */
    public function expires($time = null) {
        /** Default expires in a week. */
        if($time === null) {
            $time = time() + 604800;
        }

        $this->setExpiresAt($time)->save();

        # Refresh corresponding cache
        $tag = "homepage_app_".$this->getApplication()->getId();
        $this->cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, array($tag));

        return $this;
    }
}
