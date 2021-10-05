<?php

/**
 * Class Application_Model_Option_Value
 *
 * This object is a copy of Application_Model_Option
 * used to save per-app options/features
 *
 * @method integer getId()
 * @method integer getValueId()
 * @method getRequest()
 * @method $this setTabbarName(string $tabbarName)
 * @method Application_Model_Option_Value[] findAll($values = [], $order = null, $params = [])
 * @method $this setFolderId(integer $folderId)
 * @method $this setFolderCategoryId(integer $folderCategoryId)
 * @method $this setFolderCategoryPosition(integer $folderCategoryPosition)
 * @method integer getFolderCategoryId()
 * @method integer getOptionId()
 * @method integer getUseMyAccount()
 * @method $this setIconId(integer $iconId)
 *
 */
class Application_Model_Option_Value extends Application_Model_Option
{
    /**
     * @var array
     */
    protected $_design_colors = [
        "flat" => "#0099C7"
    ];

    /**
     * @var
     */
    protected $_metadata;

    /**
     * @var mixed|null
     */
    protected static $_editor_icon_color = null;

    /**
     * @var null|string
     */
    protected static $_editor_icon_reverse_color = null;

    /**
     * @var
     */
    protected $_background_image_url;

    /**
     * @var
     */
    protected $_background_landscape_image_url;

    /**
     * Application_Model_Option_Value constructor.
     * @param array $datas
     */
    public function __construct($datas = [])
    {
        parent::__construct($datas);
        $this->_db_table = 'Application_Model_Db_Table_Option_Value';

        if (!self::$_editor_icon_color) {
            if (array_key_exists(DESIGN_CODE, $this->_design_colors)) {
                self::$_editor_icon_color = $this->_design_colors[DESIGN_CODE];
            }
        }

        if (!self::$_editor_icon_reverse_color) {
            self::$_editor_icon_reverse_color = "#ffffff";
        }
    }

    /**
     * @return int
     */
    public function getPendingActions (): int
    {
        $object = $this->getObject();
        if (method_exists($object, 'getPendingActions')) {
            return $object->getPendingActions($this);
        }

        return 0;
    }

    /**
     * @return array
     * @throws Zend_Exception
     */
    public function getAppInitUris(): array
    {
        $object = $this->getObject();
        if (method_exists($object, 'getAppInitUris')) {
            return $object->getAppInitUris($this);
        }

        // Special uri places
        $featureUrl = $this->getUrl(null, [
            'value_id' => $this->getId()
        ], false);
        $featurePath = $this->getPath(null, [
            'value_id' => $this->getId()
        ], 'mobile');

        return [
            'featureUrl' => $featureUrl,
            'featurePath' => $featurePath,
        ];
    }

    /**
     * @param $id
     * @param null $field
     * @return $this|Application_Model_Option|null
     * @throws Zend_Exception
     */
    public function find($id, $field = null)
    {
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
    public function valueIdBelongsTo($value_id, $app_id): bool
    {
        return $this->getTable()->valueIdBelongsTo($value_id, $app_id);
    }

    /**
     * @param array $values
     * @param null $order
     * @param array $params
     * @return mixed
     */
    public function findAllWithOptionsInfos($values = [], $order = null, $params = [])
    {
        return $this->getTable()->findAllWithOptionsInfos($values, $order, $params);
    }

    /**
     * @return mixed
     */
    public function getFeaturesByApplication()
    {
        return $this->getTable()->getFeaturesByApplication();
    }

    /**
     * @param $app_id
     * @param $option_id
     * @return mixed
     */
    public function findFolderValues($app_id, $option_id)
    {
        $folderValues = $this->getTable()->getFolderValues($app_id, $option_id);
        return $folderValues;
    }

    /**
     * @return Application_Model_Option_Value
     */
    public function getDummy()
    {
        if ((isset($this) && get_class($this) == __CLASS__)) {
            $this->getApplication();

            $color = str_replace('#', '', $this->getApplication()->getBlock('tabbar')->getImageColor());
            $option = new Application_Model_Option();
            $option->find('newswall', 'code');
            $dummy = new self();

            $dummy->addData($option->getData())
                ->setTabbarName(__("Sample"))
                ->setIsDummy(1)
                ->setIsActive(1)
                ->setIconUrl(Core_Model_Url::create("template/block/colorize", ['id' => $dummy->getIconId(), 'color' => $color]))
                ->setId(0);

        } else {
            $option = new Application_Model_Option();
            $option->find('newswall', 'code');
            $dummy = new self();
            $dummy->addData($option->getData())
                ->setTabbarName(__("Sample"))
                ->setIsDummy(1)
                ->setIsActive(1)
                ->setIconUrl(Core_Model_Url::create("template/block/colorize", ['id' => $dummy->getIconId(), 'color' => '000000']))
                ->setId(0);
        }

        return $dummy;
    }

    /**
     * @return $this
     */
    public function save()
    {
        if (!$this->getId()) {
            $this->setNextPosition();
            $this->setLayoutId(1)->setIsActive(1);
        }
        return parent::save();
    }

    /**
     * @return $this|void
     */
    public function delete()
    {
        $this->getObject()->deleteFeature($this->getOptionId());

        parent::delete();
    }

    /**
     * @return mixed|null
     */
    public function getIconColor()
    {
        return self::$_editor_icon_color;
    }

    /**
     * @return null|string
     */
    public function getIconReverseColor()
    {
        return self::$_editor_icon_reverse_color;
    }

    /**
     * @return array|bool|mixed|null|string
     */
    public function isActive()
    {
        return $this->getIsActive();
    }

    /**
     * @param string $folder
     * @return string
     */
    public function getImagePathTo($folder = "")
    {
        $path = '/' . $this->getAppId() . '/features/' . $this->getCode() . '/' . $this->getId();
        if (!empty($folder)) {
            $path .= '/' . $folder;
        }
        return $path;
    }

    /**
     * @return string
     */
    public function getBackgroundImageUrl()
    {
        if (!$this->_background_image_url) {
            if ($this->getBackgroundImage() AND $this->getBackgroundImage() != 'no-image') {
                $this->_background_image_url = Application_Model_Application::getImagePath() . $this->getBackgroundImage();
            }
        }

        return $this->_background_image_url;
    }

    /**
     * @return string
     */
    public function getBackgroundLandscapeImageUrl()
    {
        if (!$this->_background_landscape_image_url) {
            if ($this->getBackgroundLandscapeImage() AND $this->getBackgroundLandscapeImage() != 'no-image') {
                $this->_background_landscape_image_url = Application_Model_Application::getImagePath() . $this->getBackgroundLandscapeImage();
            }
        }

        return $this->_background_landscape_image_url;
    }

    /**
     * @return $this
     */
    public function addOptionDatas()
    {
        if (is_numeric($this->getId())) {
            $datas = $this->getTable()->getOptionDatas($this->getOptionId());
            foreach ($datas as $key => $value) {
                if (is_null($this->getData($key))) $this->setData($key, $value);
            }
        }

        return $this;
    }

    /**
     * @param $icon_color
     */
    public static function setEditorIconColor($icon_color)
    {
        self::$_editor_icon_color = $icon_color;
    }

    /**
     * @param $icon_color
     */
    public static function setEditorIconReverseColor($icon_color)
    {
        self::$_editor_icon_reverse_color = $icon_color;
    }

    /**
     * @param $application
     * @param null $parent_id
     * @return $this
     */
    public function copyTo($application, $parent_id = NULL)
    {
        $old_value_id = $this->getId();
        $old_app_id = $this->getAppId();
        $object = $this->getObject();

        $this->setId(null)
            ->setAppId($application->getId())
            ->save();

        $this->setOldValueId($old_value_id)
            ->setOldAppId($old_app_id);

        if ($object->getId()) {
            $collection = $object->findAll(['value_id' => $this->getOldValueId()]);
            foreach ($collection as $object) {
                $object->copyTo($this);
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function setNextPosition()
    {
        $lastPosition = $this->getTable()->getLastPosition($this->getApplication()->getId());
        if (!$lastPosition) $lastPosition = 0;
        $this->setPosition(++$lastPosition);

        return $this;
    }

    /**
     * @param $category_id
     * @return int
     */
    public function getNextFolderCategoryPosition($category_id)
    {
        $lastPosition = $this->getTable()->getLastFolderCategoryPosition($category_id);
        if (!$lastPosition) $lastPosition = 0;

        return ++$lastPosition;
    }

    /**
     * @return array|mixed|null|string
     */
    public function isLocked()
    {
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
        $tag_names = [];
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
        $where = [
            $table->getAdapter()->quoteInto('value_id = ?', $this->getValueId()),
            $table->getAdapter()->quoteInto('object_id = ?', $object->getId()),
            $table->getAdapter()->quoteInto('model = ?', $model_name)
        ];
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
        $results = $matadata->findAll(['value_id' => $this->getValueId()]);
        $this->_metadata = [];
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
        $metadata->find(['value_id' => $this->getValueId(), 'code' => $code]);
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
    public function _getBackground()
    {
        return $this->__getBase64Image($this->getBackground());
    }

    /**
     * @param $base64
     * @param $option
     * @return $this
     */
    public function _setBackground($base64, $option)
    {
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
     * This method will extract any features that were inside a deleted folder!
     *
     * @param $valueId
     */
    public static function extractFromFolder($valueId)
    {
        $features = (new self)
            ->findAll([
                'folder_id = ?' => $valueId
            ]);

        foreach ($features as $feature) {
            $feature
                ->setFolderId(null)
                ->setFolderCategoryId(null)
                ->setFolderCategoryPosition(null)
                ->save();
        }
    }

    /**
     * @param $path
     * @throws Exception
     */
    public function readOption($path)
    {
        $content = file_get_contents($path);

        try {
            $dataset = Siberian_Yaml::decode($content);
        } catch (Exception $e) {
            throw new Exception("#043-01: An error occured while importing YAML dataset '$path'.");
        }

        if (isset($dataset["option"])) {
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
    public function forYaml()
    {
        $data = $this->getData();

        if ($this->getBackground()) {
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
    public function touch()
    {
        $this->setTouchedAt(time())->save();

        # Refresh corresponding cache
        $tag = "homepage_app_" . $this->getApplication()->getId();
        $this->cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, [$tag]);

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
    public function expires($time = null)
    {
        /** Default expires in a week. */
        if ($time === null) {
            $time = time() + 604800;
        }

        $this->setExpiresAt($time)->save();

        # Refresh corresponding cache
        $tag = "homepage_app_" . $this->getApplication()->getId();
        $this->cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, [$tag]);

        return $this;
    }
}
