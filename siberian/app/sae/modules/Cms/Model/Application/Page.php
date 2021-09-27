<?php

/**
 * Class Cms_Model_Application_Page
 *
 * @method Cms_Model_Db_Table_Application_Page getTable()
 */
class Cms_Model_Application_Page extends Core_Model_Default
{
    /**
     * @var bool
     */
    protected $_is_cacheable = true;
    /**
     * @var
     */
    protected $_blocks;
    /**
     * @var
     */
    protected $_metadata;
    /**
     * @var string
     */
    protected $_action_view = 'findall';

    /**
     * @var string
     */
    protected $_db_table = Cms_Model_Db_Table_Application_Page::class;

    /**
     * Returns Pages sorted by rank
     *
     * @param $value_id
     * @param $params
     * @return collection of pages
     */
    public static function findAllOrderedByRank($value_id, $params = null)
    {
        return self::findAllOrderedBy($value_id, 'rank', $params);
    }

    /**
     * Returns Pages sorted by label
     *
     * @param $value_id
     * @param $params
     * @return collection of pages
     */
    public static function findAllOrderedByLabel($value_id, $params = null)
    {
        return self::findAllOrderedBy($value_id, 'label', $params);
    }

    /**
     * @param $valueId
     * @param array $params
     * @return mixed
     */
    public function findAllWithFilters($valueId, $values, $params = [])
    {
        return $this->getTable()->findAllWithFilters($valueId, $values, $params);
    }

    /**
     * @param $option_value
     * @return bool
     */
    public function getEmbedPayload($option_value = null)
    {

        switch ($option_value->getCode()) {
            case 'places':
                $payload = [
                    'page_title' => $option_value->getTabbarName(),
                    'settings' => []
                ];

                if ($this->getId()) {

                    $payload['settings'] = [
                        'tags' => []
                    ];

                    $metadata = $option_value->getMetadatas();
                    foreach ($metadata as $meta) {
                        $payload['settings'][$meta->getCode()] = $meta->getPayload();
                    }

                    $tags = $option_value->getOwnTags(new Cms_Model_Application_Page());
                    foreach ($tags as $tag) {
                        $payload['settings']['tags'][] = strtolower(trim($tag->getName()));
                    }

                    $payload['settings']['tags'] = array_unique($payload['settings']['tags']);

                }
                break;
            default:
                $payload = false;
        }

        return $payload;

    }

    /**
     * CMS is special and used in many features
     *
     * @return array
     */
    public function getInappStates($value_id)
    {

        $option_value_model = new Application_Model_Option_Value();
        $option_value = $option_value_model->find($value_id);

        $option_model = new Application_Model_Option();
        $option = $option_model->find($option_value->getOptionId());

        # Special case 1. for Places
        if ($option->getCode() === 'places') {
            $place_model = new Places_Model_Place();
            return $place_model->getInappStates($value_id);
        }

        $in_app_states = [
            [
                'state' => 'cms-view',
                'offline' => true,
                'params' => [
                    'value_id' => $value_id,
                ],
            ],
        ];

        return $in_app_states;
    }

    /**
     * GET Feature url for app init
     *
     * @param $optionValue
     * @return array
     */
    public function getAppInitUris($optionValue)
    {
        $featureUrl = __url('/cms/mobile_page_view/index', [
            'value_id' => $optionValue->getId(),
        ]);
        $featurePath = __path('/cms/mobile_page_view/index', [
            'value_id' => $optionValue->getId(),
        ]);

        return [
            'featureUrl' => $featureUrl,
            'featurePath' => $featurePath,
        ];
    }

    /**
     * @param $option_value
     * @return array|string[]
     * @throws Zend_Exception
     */
    public function getFeaturePaths($option_value)
    {
        if (!$this->isCacheable()) {
            return [];
        }

        $value_id = $option_value->getId();
        $cache_id = "feature_paths_valueid_{$value_id}";
        if (!$result = $this->cache->load($cache_id)) {

            if ($option_value->getCode() === 'custom_page') {
                $paths = [];

                $paths[] = $option_value->getPath(
                    'find',
                    [
                        'page_id' => $this->getId(),
                        'value_id' => $option_value->getId()
                    ],
                    false
                );

                $paths[] = $option_value->getPath(
                    'findall',
                    [
                        'page_id' => $this->getId(),
                        'value_id' => $option_value->getId()
                    ],
                    false
                );

                $paths[] = $option_value->getPath(
                    'findall',
                    [
                        'value_id' => $option_value->getId()
                    ],
                    false
                );

                foreach ($this->getBlocks() as $block) {
                    $paths[] = $option_value->getPath(
                        'findblock',
                        [
                            'block_id' => $block->getId(),
                            'page_id' => $this->getId(),
                            'value_id' => $option_value->getId()
                        ],
                        false
                    );
                    $paths[] = $option_value->getPath(
                        'findblock',
                        [
                            'block_id' => $block->getId(),
                            'value_id' => $option_value->getId()
                        ],
                        false
                    );
                }
            } else {
                // Places paths
                $places = new Places_Model_Place();
                $paths = $places->getFeaturePaths($option_value);
            }

            $this->cache->save($paths, $cache_id, [
                'feature_paths',
                "feature_paths_valueid_{$value_id}"
            ]);
        } else {
            $paths = $result;
        }

        return $paths;
    }

    /**
     * @param $option_value
     * @return array|void
     */
    public function getAssetsPaths($option_value)
    {
        if (!$this->isCacheable()) {
            return [];
        }

        $value_id = $option_value->getId();
        $cache_id = "assets_paths_valueid_{$value_id}";
        if (!$result = $this->cache->load($cache_id)) {

            if ($option_value->getCode() == "custom_page") {
                $paths = [];

                $val = $this->getPictureUr();
                if (!empty($val)) {
                    $paths[] = $val;
                }

                foreach ($this->getBlocks() as $block) {
                    $data = $block->_toJson("");
                    $keys = ["icon", "image_url", "cover_url", "file_url"];

                    foreach ($keys as $key) {
                        $val = $data[$key];
                        if (!empty($val)) {
                            $paths[] = $val;
                        }
                    }

                    if (is_array($data["gallery"])) {
                        foreach ($data["gallery"] as $img) {
                            $paths[] = $img["src"];
                        }
                    }

                    if ($block->getType() == "video" && $data["video_type_id"] == "link") {
                        $paths[] = $data["url"];
                    }

                    if ($block->getType() == "text") {

                        $matches = [];
                        $regex_url = "/((?:http|https)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(?:\/[^\s\"]*)\.(?:png|gif|jpeg|jpg)+)+/";
                        preg_match_all($regex_url, $block->getContent(), $matches);

                        $matches = call_user_func_array('array_merge', $matches);

                        if ($matches && count($matches) > 1) {
                            unset($matches[0]);
                            $paths = array_merge($paths, $matches);
                        }
                    }
                }

            } else {
                // Places paths
                $places = new Places_Model_Place();
                $paths = $places->getAssetsPaths($option_value);
            }

            $this->cache->save($paths, $cache_id, [
                "assets_paths",
                "assets_paths_valueid_{$value_id}"
            ]);
        } else {
            $paths = $result;
        }

        return $paths;
    }

    /**
     * Return the collection of pages belonging to the feature($value_id) ordered by $field
     *
     * @param $value_id
     * @param $field
     * @param $params
     * @return collection of pages
     */
    private static function findAllOrderedBy($value_id, $field, $params = null)
    {
        $table = new Cms_Model_Db_Table_Application_Page();
        $select = $table->select(Zend_Db_Table::SELECT_WITH_FROM_PART);
        $select->setIntegrityCheck(false);
        $select
            ->join('cms_application_page_block', 'cms_application_page_block.page_id = cms_application_page.page_id')
            ->join('cms_application_page_block_address', 'cms_application_page_block_address.value_id = cms_application_page_block.value_id')
            ->where("cms_application_page.value_id = ?", $value_id)
            ->order("cms_application_page_block_address." . $field . " asc");

        if (is_array($params)) {
            if (isset($params["limit"]) && isset($params["offset"])) {
                $select->limit($params["limit"], $params["offset"]);
            }
        }

        return $table->fetchAll($select);
    }

    /**
     * @param $value_id
     * @param $ids
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public static function findAllByPageId($value_id, $ids)
    {
        $table = new Cms_Model_Db_Table_Application_Page();
        $select = $table->select(Zend_Db_Table::SELECT_WITH_FROM_PART);
        $select->where("cms_application_page.value_id = ?", $value_id)
            ->where("cms_application_page.page_id IN (?)", $ids);
        return $table->fetchAll($select);
    }

    /**
     * Executed when the feature is created
     *
     * @param $option_value
     */
    public function prepareFeature($option_value)
    {
        self::setPlaceOrder($option_value->getValueId(), 'true');
    }

    /**
     * Handles the saving of the places_order metadatum
     *
     * @param $value_id
     * @param $order
     */
    public static function setPlaceOrder($value_id, $order)
    {
        // Delete old metadata value
        Application_Model_Option_Value_Metadata::deleteByCode($value_id, 'places_order');
        // Replace it with the current one
        $metadatum = new Application_Model_Option_Value_Metadata();
        $metadatum->setPayload($order ? 'true' : 'false');
        $metadatum->setCode('places_order');
        $metadatum->setValueId($value_id);
        $metadatum->setType('boolean');
        $metadatum->save();
    }

    /**
     * Handles the saving of the places_order_alpha metadatum
     *
     * @param $value_id
     * @param $order
     */
    public static function setPlaceOrderAlpha($value_id, $order)
    {
        // Delete old metadata value
        Application_Model_Option_Value_Metadata::deleteByCode($value_id, 'places_order_alpha');
        // Replace it with the current one
        $metadatum = new Application_Model_Option_Value_Metadata();
        $metadatum->setPayload($order ? 'true' : 'false');
        $metadatum->setCode('places_order_alpha');
        $metadatum->setValueId($value_id);
        $metadatum->setType('boolean');
        $metadatum->save();
    }

    /**
     * @param $url
     * @return $this
     */
    public function findByUrl($url)
    {
        $this->find($url, 'url');
        return $this;
    }

    /**
     * @return Cms_Model_Application_Block[]
     */
    public function getBlocks()
    {
        if (is_null($this->_blocks) && $this->getId()) {
            $block = new Cms_Model_Application_Block();
            $this->_blocks = $block->findByPage($this->getId());
        }

        return $this->_blocks;
    }

    /**
     * @return null|string
     */
    public function getPictureUrl()
    {
        $path = Application_Model_Application::getImagePath() . $this->getPicture();
        $base_path = Application_Model_Application::getBaseImagePath() . $this->getPicture();
        return is_file($base_path) ? $path : null;
    }

    /**
     * @return null|string
     */
    public function getThumbnailUrl()
    {
        $path = Application_Model_Application::getImagePath() . $this->getThumbnail();
        $base_path = Application_Model_Application::getBaseImagePath() . $this->getThumbnail();
        return is_file($base_path) ? $path : null;
    }

    /**
     * @return $this|void
     */
    public function save()
    {
        parent::save();
        $blocks = $this->getData('block') ? $this->getData('block') : [];
        $this->getTable()->saveBlock($this->getId(), $blocks);
    }

    /**
     * No needs for saveBlocks anymore
     */
    public function save_v2()
    {
        parent::save();
    }


    /**
     * @param $option_value
     * @param $datas
     * @return Cms_Model_Application_Page
     * @throws \Siberian\Exception
     */
    public function edit_v2($option_value, $datas)
    {

        if (empty($datas['orderUniqid'])) {
            throw new \Siberian\Exception('#578-21' . __('At least one section is required to save.'));
        }

        $db = Zend_Db_Table::getDefaultAdapter();
        // Starts a transaction
        $db->beginTransaction();

        try {
            if (!$option_value) {
                throw new \Siberian\Exception('#578-01: ' . __('An error occurred while saving your page.'));
            }

            $value_id = $option_value->getId();

            # Create a new CMS Page
            $page = new Cms_Model_Application_Page();
            if (!isset($datas['page_id']) ||
                empty($datas['page_id']) ||
                ($datas['page_id'] === 'new')) {
                $page
                    ->setValueId($value_id)
                    ->save_v2() # save_v2 is a simple save, without all the old saveBlocks
                ;
            } else {
                $page->find($datas['page_id']);

                if ($page->getId() && ($page->getValueId() != $value_id)) {
                    throw new \Siberian\Exception('#578-02: ' . __('An error occurred while saving your page.'));
                }
            }

            # Places case
            $isPlaces = false;
            if (isset($datas['cms_type']) &&
                $datas['cms_type'] === 'places') {

                $page
                    ->savePlace($option_value, $datas)
                    ->setMetadata($datas['metadata'])
                    ->saveMetadata();

                $isPlaces = true;
            }

            # Page title
            if ($isPlaces &&
                empty($datas['title'])) {
                throw new \Siberian\Exception('#578-03: ' . __('Title is required.'));
            }

            $page
                ->setTitle($datas['title'])
                ->save_v2();

            # Clear all page_blocks
            $cms_page_block = new Cms_Model_Application_Page_Block();
            $cms_page_blocks = $cms_page_block->findAll([
                'page_id = ?' => $page->getId()
            ]);
            foreach ($cms_page_blocks as $cms_page_block) {
                $cms_page_block->delete();
            }

            $positions = explode(',', $datas['orderUniqid']);

            // At least one block was not saved
            $messagePartialError = [];

            $block_position = 0;
            $blocks = $datas['block'];

            $hasAddress = false;
            foreach ($positions as $uniqid) {
                $block = $blocks[$uniqid];
                $block_type = key($block);
                $values = $block[$block_type];

                switch ($block_type) {
                    case 'text':
                        $model = new Cms_Model_Application_Page_Block_Text();
                        break;
                    case 'image':
                        $model = new Cms_Model_Application_Page_Block_Image();
                        break;
                    case 'video':
                        $model = new Cms_Model_Application_Page_Block_Video();
                        break;
                    case 'address':
                        $model = new Cms_Model_Application_Page_Block_Address();

                        // Validate only if not empty!
                        if (!empty($values['website']) &&
                            !preg_match("/^https?:\/\//i", $values['website'])) {
                            $messagePartialError[] = __('Website must start with http:// or https://, invalid value have been removed!');
                            $values['website'] = '';
                        }

                        break;
                    case 'button':
                        $model = new Cms_Model_Application_Page_Block_Button();
                        break;
                    case 'file':
                        $model = new Cms_Model_Application_Page_Block_File();
                        break;
                    case 'slider':
                        $model = new Cms_Model_Application_Page_Block_Slider();
                        break;
                    case 'cover':
                        $model = new Cms_Model_Application_Page_Block_Cover();
                        break;
                    case 'source':
                        $model = new Cms_Model_Application_Page_Block_Source();
                        break;
                    default:
                        throw new \Siberian\Exception(__("This block type doesn't exists."));
                }

                $result = $model
                    ->setOptionValue($option_value)
                    ->populate($values)
                    ->createBlock($block_type, $page, $block_position)
                    ->save_v2();

                if ($result === false) {
                    $messagePartialError[] =
                        __('The block N°%s, %s was not saved, the block was either empty or invalid.', $block_position + 1, __(ucfirst($block_type)));
                } else if ($isPlaces && $block_type === 'address') {
                    $hasAddress = true;
                }

                $block_position++;
            }

            $page->setData('__is_places', $isPlaces);
            $page->setData('__has_address', $hasAddress);
            $page->setData('__invalid_blocks', $messagePartialError);

            // Everything was ok, Commit
            try {
                $db->commit();
            } catch (\Exception $eCommit) {
                // ignore
            }
        } catch (\Exception $eMain) {
            // We got an unrecoverable error, rollback
            try {
                $db->rollBack();
            } catch (\Exception $eRollback) {
                // ignore
            }

            throw $eMain;
        }

        return $page;
    }

    /**
     * @param $option_value
     * @param $design
     * @param $category
     * @deprecated
     *
     */
    public function createDummyContents($option_value, $design, $category)
    {

        $option = new Application_Model_Option();
        $option->find($option_value->getOptionId());

        $dummy_content_xml = $this->_getDummyXml($design, $category);

        // Continue if dummy is empty!
        if (!$dummy_content_xml) {
            return;
        }

        if ($option->getCode() == 'places' && $dummy_content_xml->places) {

            foreach ($dummy_content_xml->places->children() as $content) {

                $this->unsData();

                $blocks = [];
                $i = 1;
                foreach ($content->block as $block_content) {

                    $block = new Cms_Model_Application_Block();
                    $block->find((string)$block_content->type, "type");

                    $data = (array)$block_content;
                    if ($block_content->image_url) {
                        $data['image_url'] = (array)$block_content->image_url;
                        $data['image_fullsize_url'] = (array)$block_content->image_fullsize_url;
                    }
                    $data["block_id"] = $block->getId();

                    $blocks[$i++] = $data;
                }

                $this->addData((array)$content->content)
                    ->setBlock($blocks)
                    ->setValueId($option_value->getId())
                    ->save();
            }

        } else {

            $blocks = [];
            $i = 1;
            foreach ($dummy_content_xml->blocks->children() as $content) {

                $block = new Cms_Model_Application_Block();
                $block->find((string)$content->type, "type");

                $data = (array)$content;
                if ($content->image_url) {
                    $data['image_url'] = (array)$content->image_url;
                    $data['image_fullsize_url'] = (array)$content->image_fullsize_url;
                }
                $data["block_id"] = $block->getId();

                $blocks[$i++] = $data;
            }

            $this->setValueId($option_value->getId())
                ->setBlock($blocks)
                ->save();

        }
    }

    /**
     * @param $option
     */
    public function copyTo($option, $parent_id = null)
    {
        $blocks = [];

        foreach ($this->getBlocks() as $block) {
            switch ($block->getType()) {
                case 'image':
                case 'cover':
                case 'slider':
                    $library = new Cms_Model_Application_Page_Block_Image_Library();
                    $images = $library->findAll(['library_id' => $block->getLibraryId()], 'image_id ASC', null);
                    $block->unsId(null)
                        ->unsLibraryId(null)
                        ->unsImageId()
                        ->unsCoverId()
                        ->unsSliderId();
                    $new_block = $block->getData();
                    $new_block['image_url'] = [];
                    $new_block['image_fullsize_url'] = [];
                    $new_block['library_id'] = null;
                    $new_block['type'] = $block->getType();

                    foreach ($images as $image) {
                        $new_block['image_url'][] = $image->getData('image_url');
                        $new_block['image_fullsize_url'][] = $image->getData('image_url');
                    }
                    $blocks[] = $new_block;
                    break;

                case 'video':
                    $object = $block->getObject();
                    $object->setId(null);
                    $block->unsId(null)->unsVideoId();
                    $blocks[] = $block->getData() + $object->getData();
                    break;

                case 'address' :
                    $object = $block->getObject();
                    $object->setId(null);
                    $block->unsId(null)->unsAddressId();
                    $blocks[] = $block->getData() + $object->getData();
                    break;

                case 'text' :
                    $content = $block->getContent();
                    $content = preg_replace('/<a(.*)data-state=(.*)>(.*)<\/a>/mi', '', $content);
                    $block->setData("content", $content);

                case 'button' :
                    $block->unsButtonId();

                case 'file' :
                    $block->unsFileId();

                default:
                    $block->unsId(null)->unsTextId();
                    $blocks[] = $block->getData();
                    break;
            }

        }

        $this->setData('block', $blocks);
        $this->setId(null)
            ->setValueId($option->getId())
            ->save();

    }

    /**
     * Returns all the metadata associated with the current page
     *
     * @return collection           A collection of Cms_Model_Db_Table_Application_Page_Metadata
     */
    public function getMetadatas()
    {
        $matadata = new Cms_Model_Application_Page_Metadata();
        $results = $matadata->findAll(['page_id' => $this->getPageId()]);
        $this->_metadata = [];
        foreach ($results as $result) {
            array_push($this->_metadata, $result);
        }
        return $this->_metadata;
    }

    /**
     * Returns the metadatum which has the code specified.
     *
     * @param string                e.g. 'show_titles' or 'show_image'
     * @return collection           A collection of Cms_Model_Db_Table_Application_Page_Metadata
     */
    public function getMetadata($code)
    {
        $metadata = new Cms_Model_Application_Page_Metadata();
        $metadata->find(['page_id' => $this->getPageId(), 'code' => $code]);
        return $metadata;
    }

    /**
     * Sets the page metadata to the data provided.
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
     * @param $code
     * @param $payload
     * @return Cms_Model_Application_Page_Metadata
     * @throws Zend_Exception
     */
    protected function _createMetadata($code, $payload)
    {
        $meta = new Cms_Model_Application_Page_Metadata();
        $meta->setPageId($this->getPageId());
        $meta->setCode($code);
        $meta->setPayload($payload);
        return $meta;
    }

    /**
     * @param $optionValue
     * @param $datas
     * @return $this
     * @throws Zend_Exception
     * @throws \Siberian\Exception
     */
    public function savePlace($optionValue, $datas)
    {
        $picture = Siberian_Feature::saveImageForOptionDelete($optionValue, $datas['places_file']);
        $thumbnail = Siberian_Feature::saveImageForOptionDelete($optionValue, $datas['places_thumbnail']);
        $pin = Siberian_Feature::saveImageForOptionDelete($optionValue, $datas['places_pin']);
        $hidePin = filter_var($datas['hide_pin'], FILTER_VALIDATE_BOOLEAN);

        $this
            ->setTitle($datas['title'])
            ->setContent($datas['content'])
            ->setIsFeatured($datas['is_featured'])
            ->setPicture($picture)
            ->setThumbnail($thumbnail)
            ->setPin($pin)
            ->setHidePin($hidePin)
            ->setMapIcon($datas['map_icon'])
            ->setPlaceVersion(2)
            ->setTags($datas['tags'])
            ->save();

        // Clear all categories
        $pageCategories = (new Places_Model_PageCategory())->findAll(['page_id' => $this->getId()]);
        foreach ($pageCategories as $_pageCategory) {
            $_pageCategory->delete();
        }

        // Then add the new ones!
        foreach ($datas['place_categories'] as $categoryId) {
            $pageCategory = (new Places_Model_PageCategory())
                ->find(
                    [
                        'page_id' => $this->getId(),
                        'category_id' => $categoryId
                    ]);

            if (!$pageCategory->getId()) {
                $pageCategory
                    ->setPageId($this->getId())
                    ->setCategoryId($categoryId)
                    ->save();
            }
        }

        return $this;
    }
}
