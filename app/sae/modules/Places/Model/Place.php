<?php
class Places_Model_Place extends Core_Model_Default {

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Places_Model_Db_Table_Place';
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

    public static function sortPlacesByDistance($a, $b) {

        $distanceA = $a["distance"];
        $distanceB = $b["distance"];
        if ($distanceA && $distanceB) {
            if ($distanceA == $distanceB) {
                // distance are equals, keep order
                return -1;
            }
            // sort by distance ASC
            return ($distanceA > $distanceB) ? 1 : -1;
        } else {
            if ($distanceB) {
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

}
