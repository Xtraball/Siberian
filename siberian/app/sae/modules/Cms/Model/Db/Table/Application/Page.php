<?php

/**
 * Class Cms_Model_Db_Table_Application_Page
 */
class Cms_Model_Db_Table_Application_Page extends Core_Model_Db_Table
{

    /**
     * @var string
     */
    protected $_name = "cms_application_page";
    /**
     * @var string
     */
    protected $_primary = "page_id";
    /**
     * @var string
     */
    protected $_modelClass = "Cms_Model_Application_Page";

    /**
     * @param $page_id
     * @param $blocks
     */
    public function saveBlock($page_id, $blocks)
    {
        try {
            $this->beginTransaction();
            $this->_db->delete('cms_application_page_block', ['page_id = ?' => $page_id]);
            foreach ($blocks as $block) {

                $class = 'Cms_Model_Application_Page_Block_' . ucfirst($block['type']);
                $block_type = new $class();

                if (($block['type'] == 'image' || $block['type'] == 'slider' || $block['type'] == 'cover')) {
                    $this->_db->delete('cms_application_page_block_image_library', ['library_id = ?' => $block["library_id"]]);
                    unset($block["library_id"]);
                    $lib_class = 'Cms_Model_Application_Page_Block_Image_Library';
                    $lib = new $lib_class();
                    if (!empty($block["image_url"])) $block["library_id"] = $lib->findLastLibrary();
                }

                $block_type->setData($block);

                if ($block_type->isValid()) {

                    $datas = ['block_id' => $block['block_id'], 'page_id' => $page_id, 'position' => $block['position']];
                    $this->_db->insert('cms_application_page_block', $datas);
                    $block_type->setValueId($this->_db->lastInsertId())
                        ->save();

                    if (($block['type'] == 'image' || $block['type'] == 'slider' || $block['type'] == 'cover') && !empty($block["image_url"])) {
                        $lib_class = 'Cms_Model_Application_Page_Block_Image_Library';
                        foreach ($block["image_url"] as $index => $image_url) {
                            $image_fullsize_url = $block["image_fullsize_url"][$index];
                            $data_image = [
                                "library_id" => $block["library_id"],
                                "image_url" => $image_url,
                                "image_fullsize_url" => $image_fullsize_url,
                            ];
                            $lib = new $lib_class();
                            $lib->addData($data_image)->save();
                        }
                    }

                }

            }
            $this->commit();
        } catch (Exception $e) {
            $this->rollback();
        }

    }

    /**
     * @param $valueId
     * @param $values
     * @param array $params
     * @return array
     */
    public function findAllByDistance($valueId, $values, $params = [])
    {
        //$moreSearch = $values["more_search"];
        $formula = new Zend_Db_Expr("0");

        $searchByDistance = false;
        if ($values["search_by_distance"] && $values["latitude"] && $values["longitude"]) {
            $formula = Siberian_Google_Geocoding::getDistanceFormula(
                $values["latitude"],
                $values["longitude"],
                "page_block_address",
                'latitude',
                'longitude');
            $searchByDistance = true;
        }

        $select = $this->_db->select()
            ->from(["page" => "cms_application_page"], ["*", "time" => "UNIX_TIMESTAMP(page.created_at)", "distance" => $formula])
            ->join(["page_block" => "cms_application_page_block"], "page.page_id = page_block.page_id AND page_block.block_id = 4")
            ->join(["page_block_address" => "cms_application_page_block_address"], "page_block.value_id = page_block_address.value_id")
            ->limit($params["limit"], $params["offset"]);

        //if(isset($values["time"]) && ($values["time"] > 0) && (!$values["search_by_distance"] || !$values["position"])) {
        //    if($values["pull_to_refresh"]) {
        //        $select->where("UNIX_TIMESTAMP(place.created_at) > ?", $values["time"]);
        //    } else {
        //        $select->where("UNIX_TIMESTAMP(place.created_at) < ?", $values["time"]);
        //    }
        //    $select->order("time DESC");
        //}

        //if(isset($values["distance"]) && ($values["distance"] > 0) && $searchByDistance && !$moreSearch) {
        //    if($values["pull_to_refresh"]) {
        //        $select->having("distance < ?", $values["distance"]);
        //    } else {
        //        $select->having("distance > ?", $values["distance"]);
        //    }
        //}

        if ($searchByDistance) {
            /** Distance */
            //if(isset($values["radius"]) && $values["radius"] > 0) {
            //    $select->having("distance < ?", $values["radius"]*1000);
            //}
            $select->order(["distance ASC"]);
        }

        $select->where('page.value_id = ?', $valueId);

        //if(!$values["position"]) {
        //    $select->order(["time DESC"]);
        //}

        //if($moreSearch) {
        //    if(isset($values["categories"]) && is_array($values["categories"])) {
        //        $ids = [];
        //        foreach($values["categories"] as $category) {
        //            if($category["is_checked"]) {
        //                $ids[] = $category["id"];
        //            }
        //        }
//
        //        if(!empty($ids)) {
        //            $select->where("place.category_id IN (?)", $ids);
        //        }
        //    }
//
        //    if(isset($values["keywords"]) && !empty($values["keywords"])) {
        //        $keywords = explode(",", $values["keywords"]);
        //        if(sizeof($keywords) == 1) {
        //            $select->where("(place.keywords LIKE ? OR category.keywords LIKE ?)", "%$keywords[0]%");
        //        } else {
        //            for($i = 0; $i < sizeof($keywords); $i++) {
        //                if($i==0) {
        //                    $select->find("((place.keywords LIKE ? OR category.keywords LIKE ?)", "%$keywords[$i]%");
        //                } elseif($i == sizeof($keywords)-1) {
        //                    $select->where("(place.keywords LIKE ? OR category.keywords LIKE ?))", "%$keywords[$i]%");
        //                } else {
        //                    $select->where("(place.keywords LIKE ? OR category.keywords LIKE ?)", "%$keywords[$i]%");
        //                }
        //            }
        //        }
        //    }
        //}

        return $this->toModelClass($this->_db->fetchAll($select));
    }

}
