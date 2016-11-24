<?php
class Places_Model_Place extends Core_Model_Default {
    protected $select;
    protected $table;
    protected $address;
    protected $method_lookup = array(
        'text' => 'setFreeTextFilter',
        'type' => 'setTagFilter',
        'address' => 'setAddressFilter',
        'aroundyou' => 'setRadiusFilter'
    );
    protected $float_validator;
    protected $int_validator;

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Places_Model_Db_Table_Place';
        $this->float_validator = new Zend_Validate_Float();
        $this->int_validator = new Zend_Validate_Int();
        return $this;
    }

    public function copyTo($option) {

        $blocks = array();

        $page = $this->getPage();

        if($page->getId()) {

            $blocks = $page->getBlocks();

            foreach($blocks as $block) {
                switch($block->getType()) {
                    case 'image':
                        $library = new Cms_Model_Application_Page_Block_Image_Library();
                        $images = $library->findAll(array('library_id' => $block->getLibraryId()), 'image_id ASC', null);
                        $block->unsId(null)->unsLibraryId(null)->unsImageId();
                        $new_block = $block->getData();
                        $new_block['image_url'] = array();
                        $new_block['image_fullsize_url'] = array();
                        foreach($images as $image) {
                            $new_block['image_url'][] = $image->getData('image_url');
                            $new_block['image_fullsize_url'][] = $image->getData('image_fullsize_url');
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
                        $block->unsAddressId();
                    case 'text' :
                        $block->unsId(null)->unsTextId();
                        $blocks[] = $block->getData();
                        break;
                    case 'button' :
                        $block->unsId(null)->unsButtonId();
                        $blocks[] = $block->getData();
                        break;
                    default:
                        $blocks[] = $block->getData();
                        break;
                }

            }

        }

        $this->setId(null)
            ->setValueId($option->getId())
            ->save()
        ;

        if($page->getId()) {
            $page->setData('block', $blocks);
            $page->setId(null)
                ->setPageId($this->getId())
                ->save()
            ;
        }

    }

    public function getPage() {

        if(!$this->_page) {
            $this->_page = new Cms_Model_Application_Page();
            $this->_page->find($this->getId(), 'page_id');
        }

        return $this->_page;

    }

    public function setPage($page)
    {
        $this->_page = $page;
        return $this;
    }

    public static function sortPlacesByDistance($a, $b) {

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

    public function getFeaturePaths($option_value) {
        if(!$this->isCachable()) return array();

        $paths = array();
        $value_id = $option_value->getId();

        // Places list paths
        $params = array(
            "value_id" => $value_id,
            "latitude" => 0,
            "longitude" => 0
        );
        $paths[] = $option_value->getPath("places/mobile_list/findall", $params, false);

        // Places view paths
        $pageRepository = new Cms_Model_Application_Page();
        $pages = $pageRepository->findAll(array('value_id' => $value_id));

        foreach($pages as $page) {

            $blocks = $page->getBlocks();

            foreach($blocks as $block) {

                if($block->getType() == "address") {
                    $params = array(
                        "page_id" => $page->getId(),
                        "value_id" => $value_id
                    );
                    $paths[] = $option_value->getPath("cms/mobile_page_view/findall", $params, false);
                }

            }

        }

        return $paths;
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
    public function distance($position) {
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
        $latitude = gettype($position)=="array" ? $position['latitude'] :$position->latitude;
        $longitude = gettype($position)=="array" ? $position['longitude'] :$position->longitude;
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
     * Returns the json representation of the page.
     *
     * @param $controller
     * @param $position
     * @return array
     */
    public function asJson($controller, $position) {
        $address = $this->getAddressBlock();

        $url = $this->getApplication()->useIonicDesign() ?
            $controller->getUrl("places/mobile_details/index", array("value_id" => $this->getValueId(), "place_id" => $this->getId())) :
            $controller->getPath("cms/mobile_page_view/index", array("value_id" => $this->getValueId(), "page_id" => $this->getId()));

        $entity = $this->int_validator->isValid($this->getId()) ? $this : $this->_page;

        $representation = array(
            "id" => $entity->getPageId(),
            "title" => $entity->getTitle(),
            "subtitle" => $entity->getContent(),
            "picture" => $entity->getPictureUrl() ? $controller->getRequest()->getBaseUrl() . $entity->getPictureUrl() : null,
            "url" => $url,
            "address" => array(
                "id" => $address->getId(),
                "position" => $address->getPosition(),
                "block_id" => $address->getBlockId(),
                "label" => $address->getLabel(),
                "address" => $address->getAddress(),
                "latitude" => $address->getLatitude(),
                "longitude" => $address->getLongitude(),
                "show_address" => $address->getShowAddress(),
                "show_geolocation_button" => $address->getGeolocationButton()
            )
        );

        // only one address per place
        $representation['show_image'] = $this->getPage()->getMetadataValue('show_image');
        $representation['show_titles'] = $this->getPage()->getMetadataValue('show_titles');
        $representation["distance"] = $this->distance($position);

        return $representation;
    }

}
