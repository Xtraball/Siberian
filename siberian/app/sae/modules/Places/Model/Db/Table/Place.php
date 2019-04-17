<?php

/**
 * Class Places_Model_Db_Table_Place
 */
class Places_Model_Db_Table_Place extends Core_Model_Db_Table
{
    /**
     * @var string
     */
    protected $_name = 'cms_application_page';

    /**
     * @var string
     */
    protected $_primary = 'page_id';

    /**
     * @param $valueId
     * @param $values
     * @param array $params
     * @return array
     */
    public function findAllWithFilters($valueId, $values, $params = [])
    {
        $formula = new Zend_Db_Expr("0");
        $sortingType = $params["sortingType"];

        $searchByDistance = false;
        if ($values["search_by_distance"] && $values["latitude"] && $values["longitude"]) {
            $formula = Siberian_Google_Geocoding::getDistanceFormula(
                $values["latitude"],
                $values["longitude"],
                "page_block_address",
                "latitude",
                "longitude");
            $searchByDistance = true;
            $sortingType = "distance";
        } else {
            // If we don't have geo, remove distance sorting, fallback on alpha
            if ($sortingType === "distance") {
                $sortingType = "alpha";
            }
        }

        $select = $this->_db->select()
            ->from(["page" => "cms_application_page"], ["*", "time" => "UNIX_TIMESTAMP(page.created_at)", "distance" => $formula])
            ->join(["page_block" => "cms_application_page_block"], "page.page_id = page_block.page_id AND page_block.block_id = 4")
            ->join(["page_block_address" => "cms_application_page_block_address"], "page_block.value_id = page_block_address.value_id")
            ->limit($params["limit"], $params["offset"]);

        // Category filter
        if (array_key_exists("categories", $params) && !empty($params["categories"])) {
            $categories = explode(",", $params["categories"]);

            $select
                ->join(["page_category" => "place_page_category"], "page_category.page_id = page.page_id", [])
                ->join(["category" => "place_category"], "category.category_id = page_category.category_id", []);

            $select->where("category.category_id IN (?)", $categories);
        } else {
            $select
                ->joinLeft(["page_category" => "place_page_category"], "page_category.page_id = page.page_id", [])
                ->joinLeft(["category" => "place_category"], "category.category_id = page_category.category_id", []);
        }

        // Fulltext search
        if (array_key_exists("fulltext", $params)) {
            $fulltext = trim($params["fulltext"]);
            if (!empty($fulltext)) {
                $terms = explode(" ", $fulltext);
                foreach ($terms as $term) {
                    $select
                        ->where("(page.title LIKE ?", "%{$term}%")
                        ->orWhere("page.content LIKE ?", "%{$term}%")
                        ->orWhere("page.tags LIKE ?", "%{$term}%")
                        ->orWhere("page_block_address.label LIKE ?", "%{$term}%")
                        ->orWhere("page_block_address.address LIKE ?", "%{$term}%")
                        ->orWhere("page_block_address.phone LIKE ?", "%{$term}%")
                        ->orWhere("page_block_address.website LIKE ?", "%{$term}%")
                        ->orWhere("category.title LIKE ?", "%{$term}%")
                        ->orWhere("category.subtitle LIKE ?)", "%{$term}%")
                    ;
                }

            }
        }

        switch ($sortingType) {
            case "alpha":
                $select->order(["page.title ASC"]);
                break;
            case "date":
                $select->order(["page.page_id DESC"]);
                break;
            case "distance":
                $select->order(["distance ASC"]);
                break;
        }

        if ($searchByDistance && $sortingType === "distance") {
            $select->order(["distance ASC"]);
        }

        $select
            ->where('page.value_id = ?', $valueId)
            ->distinct("page.page_id");

        return $this->toModelClass($this->_db->fetchAll($select));
    }

}