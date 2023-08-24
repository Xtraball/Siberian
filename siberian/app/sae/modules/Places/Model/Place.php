<?php

use Core\Model\Base;
use Siberian\Json;

/**
 * Class Places_Model_Place
 *
 * @method Places_Model_Db_Table_Place getTable()
 */
class Places_Model_Place extends Base
{

    /**
     * @var array
     */
    public $cache_tags = [
        "feature_places",
    ];

    /**
     * @var null
     */
    public $_blocks;

    /**
     * @var
     */
    protected $select;

    /**
     * @var
     */
    protected $table;

    /**
     * @var
     */
    protected $address;

    /**
     * @var array
     */
    protected $method_lookup = [
        'text' => 'setFreeTextFilter',
        'type' => 'setTagFilter',
        'address' => 'setAddressFilter',
        'aroundyou' => 'setRadiusFilter',
    ];

    /**
     * @var Zend_Validate_Float
     */
    protected $float_validator;

    /**
     * @var Zend_Validate_Int
     */
    protected $int_validator;

    /**
     * @var bool
     */
    protected $_is_cacheable = false;

    /**
     * @var string
     */
    protected $_db_table = Places_Model_Db_Table_Place::class;

    /**
     * Places_Model_Place constructor.
     * @param array $params
     * @throws Zend_Exception
     */
    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->float_validator = new Zend_Validate_Float();
        $this->int_validator = new Zend_Validate_Int();
    }

    /**
     * @param $optionValue
     * @return $this
     */
    public function prepareFeature($optionValue)
    {
        // Set default settings
        $defaults = [
            "default_page" => "places",
            "default_layout" => "place-100",
            "distance_unit" => "km",
            "listImagePriority" => "thumbnail",
            "defaultPin" => "pin",
        ];

        $optionValue
            ->setSettings(Json::encode($defaults))
            ->save();

        return $this;
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
     * @param $valueId
     * @param array $values
     * @param array $params
     * @return mixed
     */
    public function findAllMapWithFilters($valueId, $values = [], $params = [])
    {
        return $this->getTable()->findAllMapWithFilters($valueId, $values, $params);
    }

    /**
     * @return string full,none,partial
     */
    public function availableOffline()
    {
        return "partial";
    }

    /**
     * @param null $optionValue
     * @return array|bool
     * @throws Zend_Exception
     */
    public function getEmbedPayload($optionValue = null)
    {
        $valueId = $optionValue->getId();
        $payload = [
            "page_title" => $optionValue->getTabbarName(),
            "settings" => [],
        ];

        if ($this->getId()) {
            $payload["settings"] = [
                "categories" => [],
            ];

            $metadata = $optionValue->getMetadatas();
            foreach ($metadata as $meta) {
                $payload["settings"][$meta->getCode()] = $meta->getPayload();
            }

            try {
                $settings = \Siberian_Json::decode($optionValue->getSettings());
            } catch (\Exception $exception) {
                $settings = [];
            }

            $payload["settings"] = array_merge($payload["settings"], $settings);

            $categories = (new Places_Model_Category())
                ->findAll(['value_id' => $valueId], 'position ASC');

            foreach ($categories as $category) {
                $payload["settings"]["categories"][] = [
                    'id' => (integer)$category->getId(),
                    'title' => (string)$category->getTitle(),
                    'subtitle' => (string)$category->getSubtitle(),
                    'picture' => (string)$category->getPicture(),
                ];
            }
        }

        return $payload;
    }

    /**
     * @param $valueId
     * @return array|bool
     * @throws Zend_Exception
     */
    public function getInappStates($valueId)
    {
        $mapView = [
            "label" => p__("places", "Map view"),
            "state" => "places-list-map",
            "offline" => false,
            "params" => [
                "value_id" => $valueId,
            ],
        ];

        $pages = (new Cms_Model_Application_Page())->findAll([
            "value_id" => $valueId,
        ], null, null);

        $categories = (new Places_Model_Category())->findAll([
            "value_id" => $valueId,
        ], null, null);

        $childCats = [];
        foreach ($categories as $category) {
            $childCats[] = [
                "label" => p__("places", "[Category]") . " " . $category->getTitle(),
                "state" => "places-list",
                "offline" => true,
                "params" => [
                    "value_id" => $valueId,
                    "category_id" => $category->getId(),
                ],
            ];
        }

        $childPlaces = [];
        foreach ($pages as $page) {
            $childPlaces[] = [
                "label" => p__("places", "[Place]") . " " . $page->getTitle(),
                "state" => "places-view",
                "offline" => true,
                "params" => [
                    "value_id" => $valueId,
                    "page_id" => $page->getId(),
                ],
            ];
        }

        $inAppStates = [
            [
                "state" => "places-list",
                "offline" => true,
                "params" => [
                    "value_id" => $valueId,
                ],
                "childrens" => [
                    $mapView,
                    [
                        "label" => p__("places", "All categories view"),
                        "state" => "places-categories",
                        "offline" => true,
                        "params" => [
                            "value_id" => $valueId,
                        ],
                        "childrens" => $childCats,
                    ],
                    [
                        "label" => p__("places", "All places view"),
                        "state" => "places-list",
                        "offline" => true,
                        "params" => [
                            "value_id" => $valueId,
                        ],
                        "childrens" => $childPlaces,
                    ],
                ],
            ],
        ];

        return $inAppStates;
    }


    /**
     * @param $option
     * @return Base|void
     */
    public function copyTo($option, $parent_id = null)
    {
    }

    /**
     * @return Cms_Model_Application_Page
     * @throws Zend_Exception
     */
    public function getPage()
    {

        if (!$this->_page) {
            $this->_page = new Cms_Model_Application_Page();
            $this->_page->find($this->getId(), 'page_id');
        }

        return $this->_page;

    }

    /**
     * @param $page
     * @return $this
     */
    public function setPage($page)
    {
        $this->_page = $page;
        return $this;
    }

    /**
     * @param $a
     * @param $b
     * @return int
     */
    public static function sortPlacesByDistance($a, $b)
    {

        $distanceA = $a["distance"];
        $distanceB = $b["distance"];
        $validator = new Zend_Validate_Float();
        if ($validator->isValid($distanceA) && $validator->isValid($distanceB)) {
            if ($distanceA == $distanceB) {
                // distance are equals, keep order
                return -1;
            }
            // sort by distance ASC
            return ($distanceA > $distanceB) ? 1 : -1;
        } else {
            if ($validator->isValid($distanceB)) {
                return 1;
            }
            return -1;
        }
    }

    /**
     * @param $a
     * @param $b
     * @return int|lt
     */
    public static function sortPlacesByLabel($a, $b)
    {
        return strcmp($a["title"], $b["title"]);
    }

    /**
     * Calculates the distance between a point X(lat1, lon1) and Y(lat2, lon2)
     * Distance in km
     *
     * @param $lat1
     * @param $lon1
     * @param $lat2
     * @param $lon2
     * @return float
     */
    public function distanceBetween($lat1, $lon1, $lat2, $lon2)
    {
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        return round($miles * 1.609344);
    }

    /**
     * Returns the address associated with the current place
     *
     * @return Cms_Model_Application_Page_Block_Address
     */
    public function getAddressBlock()
    {
        if (!$this->address) {
            foreach ($this->getPage()->getBlocks() as $block) {
                if ($block->getType() == "address") {
                    $this->address = $block;
                    break;
                }
            }
        }

        return $this->address;
    }

    /**
     * Returns the feature to which the current page belongs
     *
     * @return Application_Model_Option_Value
     */
    public function getOptionValue()
    {
        $value = new Application_Model_Option_Value();
        $value->find($this->getValueId());
        return $value;
    }

    /**
     * Claculates the distance between the current place and a given position
     * If the place has no position specified then return -1
     *
     * @param $position
     * @return float
     */
    public function distance($position)
    {
        $latitude = $position['latitude'];
        $longitude = $position['longitude'];
        $block = $this->getAddressBlock();
        if ($latitude &&
            $longitude &&
            $block->getLatitude() &&
            $block->getLongitude()
        ) {
            return $this->distanceBetween($latitude, $longitude, $block->getLatitude(), $block->getLongitude());
        }
        return -1;
    }

    /**
     * Returns true if the specified position is near the place's position
     * A position is close to a place if if their distance is less than a radius specified by the admin.
     * If the radius is not specified then this returns false
     * If the place's position is missing then return false
     * If position is missing then return false
     *
     * @param $position
     * @return bool
     */
    public function near($position)
    {
        // If position is missing then return true
        if (!$this->float_validator->isValid($position['latitude']) || !$this->float_validator->isValid($position['longitude'])) {
            return false;
        }
        $radius = $this->_getReadius();
        $distance = $this->distance($position);
        // If the radius is not specified or the distance is inferior to the radius then return true
        return $this->float_validator->isValid($radius) && $distance >= 0 && $distance <= $radius;
    }

    /**
     * Return the radius for the search around you functionality
     *
     * @return float
     */
    private function _getReadius()
    {
        return $this->getOptionValue()->getMetadataValue('search_aroundyou_radius');
    }

    /**
     * Based on a list of parameters (i.e. text, tag, address, or around you) find the corresponding pages
     *
     * @param $terms
     * @param $value_id
     * @return mixed
     */
    public function search($terms, $value_id)
    {
        $this->setupQuery();
        // Foreach term find the appropriate method to filter pages and apply it
        foreach ($terms as $name => $term) {
            if ($term && array_key_exists($name, $this->method_lookup)) {
                $method = $this->method_lookup[$name];
                $this->$method($term);
            }
        }

        /** Search only in places belonging to the current application */
        $this->select->where("cms_application_page.value_id = ?", $value_id);

        return $this->table->fetchAll($this->select);
    }

    /**
     * Given the position, find the near places.
     * Places are near if they're at a distance inferior to a certain radius
     *
     * @param $position
     * @return $this
     */
    public function setRadiusFilter($position)
    {
        $latitude = gettype($position) == "array" ? $position['latitude'] : $position->latitude;
        $longitude = gettype($position) == "array" ? $position['longitude'] : $position->longitude;
        if ($this->float_validator->isValid($latitude) && $this->float_validator->isValid($longitude)) {
            $this->select->join('cms_application_page_block', 'cms_application_page_block.page_id = cms_application_page.page_id')
                ->join('cms_application_page_block_address', 'cms_application_page_block_address.value_id = cms_application_page_block.value_id')
                ->join('application_option_value', 'application_option_value.value_id = cms_application_page.value_id')
                ->join('application_option_value_metadata', 'application_option_value_metadata.value_id = application_option_value.value_id')
                ->where('application_option_value_metadata.code = ?', 'search_aroundyou_radius')
                ->where("
                        111.1111 * DEGREES(ACOS(COS(RADIANS($latitude)) *
                        COS(RADIANS(cms_application_page_block_address.latitude)) *
                        COS(RADIANS($longitude - cms_application_page_block_address.longitude)) +
                        SIN(RADIANS($latitude)) * SIN(RADIANS(cms_application_page_block_address.latitude)))) <  
                        cast(application_option_value_metadata.payload AS DECIMAL(10,2))
                    ");
        }
        return $this;
    }

    /**
     * Creates the core select query which is used to build more sophisticated queries
     *
     * @return $this
     */
    public function setupQuery()
    {
        $this->table = new Cms_Model_Db_Table_Application_Page();
        $this->select = $this->table->select(Zend_Db_Table::SELECT_WITH_FROM_PART);
        $this->select->setIntegrityCheck(false);
        return $this;
    }

    /**
     * Select pages which has an address that contains the term in its name and description
     *
     * @param $term
     * @return $this
     */
    public function setAddressFilter($term)
    {
        $this->select->join('cms_application_page_block', 'cms_application_page_block.page_id = cms_application_page.page_id')
            ->join('cms_application_page_block_address', 'cms_application_page_block_address.value_id = cms_application_page_block.value_id')
            ->where("cms_application_page_block_address.address like '%$term%' OR cms_application_page_block_address.label like '%$term%'");
        return $this;
    }

    /**
     * Selects pages which contain the term in their title or content
     *
     * @param $term
     * @return $this
     */
    public function setFreeTextFilter($term)
    {
        $this->select->where("cms_application_page.title like '%$term%' OR cms_application_page.content like '%$term%'");
        return $this;
    }

    /**
     * Selects the pages which have the term as a tag
     *
     * @param $term
     * @return $this
     */
    public function setTagFilter($term)
    {
        $this->select->join('application_tagoption', 'application_tagoption.object_id = cms_application_page.page_id')
            ->join('application_tag', 'application_tagoption.tag_id = application_tag.tag_id')
            ->where('application_tagoption.model = ?', 'Cms_Model_Application_Page')
            ->where('application_tag.name = ?', $term);
        return $this;
    }

    /**
     * @param $controller
     * @param $position
     * @param $optionValue
     * @param string $base_url
     * @return array|bool
     * @throws Zend_Exception
     */
    public function asJson($controller, $position, $optionValue, $base_url = "")
    {
        $address = $this->getAddressBlock();

        if (!$address) {
            return false;
        }

        $url = $controller->getPath("cms/mobile_page_view/index", [
            "value_id" => $this->getPage()->getValueId(),
            "page_id" => $this->getPage()->getId(),
        ]);

        $page = new Cms_Model_Application_Page();
        $page->find($this->getPage()->getId());

        $blocks = $page->getBlocks();
        $json = [];

        foreach ($blocks as $block) {
            $json[] = $block->_toJson($base_url);
        }

        $entity = $this->int_validator->isValid($this->getId()) ? $this : $this->_page;

        $distanceUnit = $optionValue->getMetadataValue('distance_unit');
        switch ($distanceUnit) {
            case 'km':
            default:
                $distance = round($this->getPage()->getDistance() / 1000, 2);
                break;
            case 'mi':
                $distance = round(($this->getPage()->getDistance() / 1000) * 0.621371, 2);
                break;
        }

        $embed_payload = [
            "blocks" => $json,
            "page" => [
                "title" => $entity->getTitle(),
                "subtitle" => $entity->getContent(),
                "picture" => $entity->getPictureUrl() ? $controller->getRequest()->getBaseUrl() . $entity->getPictureUrl() : null,
                "show_image" => (boolean)$this->getPage()->getMetadataValue('show_image'),
                "show_titles" => (boolean)$this->getPage()->getMetadataValue('show_titles'),
            ],
            "page_title" => $page->getTitle() ? $page->getTitle() : $optionValue->getTabbarName(),
            "picture" => $entity->getPictureUrl() ? $controller->getRequest()->getBaseUrl() . $entity->getPictureUrl() : null,
            "social_sharing_active" => (boolean)$optionValue->getSocialSharingIsActive(),
        ];

        $representation = [
            "id" => (integer)$entity->getPageId(),
            "title" => $entity->getTitle(),
            "subtitle" => $entity->getContent(),
            "picture" => $entity->getPictureUrl() ? $controller->getRequest()->getBaseUrl() . $entity->getPictureUrl() : null,
            "thumbnail" => $entity->getThumbnailUrl() ? $controller->getRequest()->getBaseUrl() . $entity->getThumbnailUrl() : null,
            "url" => $url,
            "address" => [
                "id" => (integer)$address->getId(),
                "position" => $address->getPosition(),
                "block_id" => (integer)$address->getBlockId(),
                "label" => $address->getLabel(),
                "address" => $address->getAddress(),
                "phone" => $address->getPhone(),
                "website" => $address->getWebsite(),
                "latitude" => (float)$address->getLatitude(),
                "longitude" => (float)$address->getLongitude(),
                "show_phone" => (boolean)$address->getShowPhone(),
                "show_website" => (boolean)$address->getShowWebsite(),
                "show_address" => (boolean)$address->getShowAddress(),
                "show_geolocation_button" => (boolean)$address->getShowGeolocationButton(),
            ],
            "show_image" => (boolean)$this->getPage()->getMetadataValue('show_image'),
            "show_titles" => (boolean)$this->getPage()->getMetadataValue('show_titles'),
            "distance" => $distance,
            "distanceUnit" => $distanceUnit,
            "embed_payload" => $embed_payload,
        ];

        return $representation;
    }

    /**
     * @param $code
     * @return null
     * @throws Zend_Exception
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
     * @param $code
     * @return $this|null
     * @throws Zend_Exception
     */
    public function getMetadata($code)
    {
        $metadata = (new Cms_Model_Application_Page_Metadata())
            ->find(
                [
                    'page_id' => $this->getPageId(),
                    'code' => $code,
                ]
            );
        return $metadata;
    }

    /**
     * @param $optionValue
     * @param string $baseUrl
     * @return array|bool
     * @throws Zend_Exception
     */
    public function toJson($optionValue = null, $baseUrl = "")
    {
        $defaultSettings = [
            "distance_unit" => "km",
            "default_page" => "places",
            "default_layout" => "place-100",
            "show_featured" => "0",
            "featured_label" => "",
            "show_non_featured" => "0",
            "non_featured_label" => "",
            "listImagePriority" => "thumbnail",
        ];

        $valueId = $optionValue->getId();
        $address = $this->getAddressBlock();
        if (!$address) {
            return false;
        }

        $blocks = $this->getBlocks();
        $json = [];

        foreach ($blocks as $block) {
            $json[] = $block->_toJson($baseUrl);
        }

        try {
            $settings = \Siberian_Json::decode($optionValue->getSettings());
            $settings = array_merge($defaultSettings, $settings);
        } catch (\Exception $e) {
            $settings = $defaultSettings;
        }

        switch ($settings["distance_unit"]) {
            case 'km':
            default:
                $distance = round($this->getDistance() / 1000, 2);
                break;
            case 'mi':
                $distance = round(($this->getDistance() / 1000) * 0.621371, 2);
                break;
        }

        $thumbnail = null;
        if (!empty($this->getThumbnailUrl())) {
            $thumbnail = $baseUrl . $this->getThumbnailUrl();
        }

        $picture = null;
        if (!empty($this->getPictureUrl())) {
            $picture = $baseUrl . $this->getPictureUrl();
        }

        $pin = null;
        if (!empty($this->getPinUrl())) {
            $pin = $baseUrl . $this->getPinUrl();
        }

        $embedPayload = [
            "blocks" => $json,
            "page" => [
                "title" => $this->getTitle(),
                "subtitle" => $this->getContent(),
                "picture" => $picture,
                "pin" => $pin,
                "show_image" => (boolean)$this->getMetadataValue('show_image'),
                "show_titles" => (boolean)$this->getMetadataValue('show_titles'),
                "show_subtitle" => (boolean)$this->getMetadataValue('show_subtitle'),
                "mapIcon" => $this->getMapIcon(),
            ],
            "page_title" => $this->getTitle(),
            "picture" => $picture,
            "social_sharing_active" => (boolean)$optionValue->getSocialSharingIsActive(),
        ];

        $representation = [
            "id" => (integer)$this->getId(),
            "title" => $this->getTitle(),
            "subtitle" => $this->getContent(),
            "picture" => $picture,
            "thumbnail" => $thumbnail,
            "pin" => $pin,
            "url" => "/places/mobile_list/index/value_id/{$valueId}/category_id/0",
            "address" => [
                "id" => (integer)$address->getId(),
                "position" => $address->getPosition(),
                "block_id" => (integer)$address->getBlockId(),
                "label" => $address->getLabel(),
                "address" => $address->getAddress(),
                "phone" => $address->getPhone(),
                "website" => $address->getWebsite(),
                "latitude" => (float)$address->getLatitude(),
                "longitude" => (float)$address->getLongitude(),
                "show_phone" => (boolean)$address->getShowPhone(),
                "show_website" => (boolean)$address->getShowWebsite(),
                "show_address" => (boolean)$address->getShowAddress(),
                "show_geolocation_button" => (boolean)$address->getShowGeolocationButton(),
            ],
            "show_image" => (boolean)$this->getMetadataValue('show_image'),
            "show_titles" => (boolean)$this->getMetadataValue('show_titles'),
            "show_subtitle" => (boolean)$this->getMetadataValue('show_subtitle'),
            "mapIcon" => $this->getMapIcon(),
            "distance" => $distance,
            "distanceUnit" => $settings["distance_unit"],
            "embed_payload" => $embedPayload,
        ];

        return $representation;
    }

    /**
     * @param $controller
     * @param $position
     * @param $option_value
     * @param string $base_url
     * @return array|bool
     * @throws Zend_Exception
     */
    public function asMapJson($controller, $position, $option_value, $base_url = "")
    {
        $address = $this->getAddressBlock();
        $page = $this->getPage();

        if (!$address) {
            return false;
        }

        # Compress homepage default
        $picture_b64 = null;
        if (!(boolean)$this->getPage()->getMetadataValue('show_picto')) {
            if ($page->getPictureUrl()) {
                $picture = Core_Model_Directory::getBasePathTo($page->getPictureUrl());
                $picture_b64 = Siberian_Image::open($picture)->cropResize(64)->inline();
            }
        }

        $payload = [
            "id" => (integer)$page->getPageId(),
            "title" => $page->getTitle(),
            "picture" => $picture_b64,
            "address" => [
                "address" => $address->getAddress(),
                "latitude" => (float)$address->getLatitude(),
                "longitude" => (float)$address->getLongitude(),
            ],
        ];

        return $payload;
    }

    /**
     * @return Cms_Model_Application_Block[]
     */
    public function getBlocks()
    {
        if (is_null($this->_blocks) && $this->getId()) {
            $this->_blocks = (new Cms_Model_Application_Block())
                ->findByPage($this->getId());
        }

        return $this->_blocks;
    }

    /**
     * @return null|string
     */
    public function getPictureUrl()
    {
        $path = Application_Model_Application::getImagePath() . $this->getPicture();
        $basePath = Application_Model_Application::getBaseImagePath() . $this->getPicture();
        return is_file($basePath) ? $path : null;
    }

    /**
     * @return null|string
     */
    public function getThumbnailUrl()
    {
        $path = Application_Model_Application::getImagePath() . $this->getThumbnail();
        $basePath = Application_Model_Application::getBaseImagePath() . $this->getThumbnail();
        return is_file($basePath) ? $path : null;
    }

    /**
     * @return null|string
     */
    public function getPinUrl()
    {
        $path = Application_Model_Application::getImagePath() . $this->getPin();
        $basePath = Application_Model_Application::getBaseImagePath() . $this->getPin();
        return is_file($basePath) ? $path : null;
    }

    /**
     * @return array
     */
    public function getTags()
    {
        $tags = explode(",", $this->getData('tags'));

        return $tags;
    }

    /**
     * @param $tags
     * @return $this
     */
    public function setTags($tags)
    {
        $tags = array_unique(array_filter($tags));

        return $this->setData('tags', join(',', $tags));
    }

    /**
     * @param $newTag
     * @return Places_Model_Place
     */
    public function addTag($newTag)
    {
        return $this->addTags([$newTag]);
    }

    /**
     * @param $newTags
     * @return $this
     */
    public function addTags($newTags)
    {
        $tags = $this->getTags();
        $tags = array_merge($tags, $newTags);

        return $this->setTags($tags);
    }

    /**
     * GET Feature url for app init
     *
     * @param $optionValue
     * @return array
     */
    public function getAppInitUris($optionValue)
    {
        try {
            $settings = Json::decode($optionValue->getSettings());
        } catch (\Exception $e) {
            $settings = [];
        }

        // Special feature for places!
        if (array_key_exists("default_page", $settings)) {
            switch ($settings["default_page"]) {
                case "categories":
                    $featureUrl = __url("/places/mobile_list/categories", [
                        "value_id" => $optionValue->getId(),
                    ]);
                    $featurePath = __path("/places/mobile_list/categories", [
                        "value_id" => $optionValue->getId(),
                    ]);
                    break;
                case "map":
                    $featureUrl = __url("/places/mobile_list_map/index", [
                        "value_id" => $optionValue->getId(),
                    ]);
                    $featurePath = __path("/places/mobile_list_map/index", [
                        "value_id" => $optionValue->getId(),
                    ]);
                    break;
                case "places":
                default:
                    $featureUrl = __url("/places/mobile_list/index", [
                        "value_id" => $optionValue->getId(),
                        "category_id" => "",
                    ]);
                    $featurePath = __path("/places/mobile_list/index", [
                        "value_id" => $optionValue->getId(),
                        "category_id" => "",
                    ]);
                    break;
            }
        } else {
            $featureUrl = __url("/places/mobile_list/index", [
                "value_id" => $this->getValueId(),
                "category_id" => "",
            ]);
            $featurePath = __path("/places/mobile_list/index", [
                "value_id" => $this->getValueId(),
                "category_id" => "",
            ]);
        }

        return [
            "featureUrl" => $featureUrl,
            "featurePath" => $featurePath,
        ];
    }

}
