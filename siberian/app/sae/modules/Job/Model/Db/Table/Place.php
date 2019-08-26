<?php

use Siberian_Google_Geocoding as Geocoding;

/**
 * Class Job_Model_Db_Table_Place
 */
class Job_Model_Db_Table_Place extends Core_Model_Db_Table {

    /**
     * @var string
     */
    protected $_name = "job_place";
    /**
     * @var string
     */
    protected $_primary = "place_id";

    /**
     * @deprecated
     *
     * @param $values
     * @param $order
     * @param $params
     * @return array
     */
    public function findActive($values, $order, $params) {
        $more_search = $values["more_search"];
        $formula = new Zend_Db_Expr("0");

        $search_by_distance = false;
        if($values["search_by_distance"] && $values["latitude"] && $values["longitude"]) {
            $formula = Geocoding::getDistanceFormula($values["latitude"], $values["longitude"], "place", $lat_name = 'latitude', $long_name = 'longitude');
            $search_by_distance = true;
        }

        $select = $this->_db->select()
            ->from(["place" => "job_place"], ["*", "time" => "UNIX_TIMESTAMP(place.created_at)", "distance" => $formula])
            ->join(["company" => "job_company"], "place.company_id = company.company_id", ["company_logo" => "logo", "company_name" => "name", "company_location" => "location"])
            ->join(["job" => "job"], "job.job_id = company.job_id")
            ->joinLeft(["category" => "job_category"], "category.category_id = place.category_id", [])
            ->where("company.is_active = ?", true)
            ->where("place.is_active = ?", true)
            ->where("job.value_id = ?", $values["value_id"])
            ->limit($params["limit"])
        ;

        if(isset($values["time"]) && ($values["time"] > 0) && (!$values["search_by_distance"] || !$values["position"])) {
            if($values["pull_to_refresh"]) {
                $select->where("UNIX_TIMESTAMP(place.created_at) > ?", $values["time"]);
            } else {
                $select->where("UNIX_TIMESTAMP(place.created_at) < ?", $values["time"]);
            }
            $select->order("time DESC");
        }

        if(isset($values["distance"]) && ($values["distance"] > 0) && $search_by_distance && !$more_search) {
            if($values["pull_to_refresh"]) {
                $select->having("distance < ?", $values["distance"]);
            } else {
                $select->having("distance > ?", $values["distance"]);
            }
        }

        if($search_by_distance && $values["position"]) {
            /** Distance */
            if(isset($values["radius"]) && $values["radius"] > 0) {
                $select->having("distance < ?", $values["radius"]*1000);
            }
            $select->order(["distance ASC", "time DESC"]);
        }

        if(!$values["position"]) {
            $select->order(["time DESC"]);
        }

        if($more_search) {
            if(isset($values["categories"]) && is_array($values["categories"])) {
                $ids = [];
                foreach($values["categories"] as $category) {
                    if($category["is_checked"]) {
                        $ids[] = $category["id"];
                    }
                }

                if(!empty($ids)) {
                    $select->where("place.category_id IN (?)", $ids);
                }
            }

            if(isset($values["keywords"]) && !empty($values["keywords"])) {
                $keywords = explode(",", $values["keywords"]);
                if(sizeof($keywords) == 1) {
                    $select->where("(place.keywords LIKE ? OR category.keywords LIKE ?)", "%$keywords[0]%");
                } else {
                    for($i = 0; $i < sizeof($keywords); $i++) {
                        if($i==0) {
                            $select->find("((place.keywords LIKE ? OR category.keywords LIKE ?)", "%$keywords[$i]%");
                        } elseif($i == sizeof($keywords)-1) {
                            $select->where("(place.keywords LIKE ? OR category.keywords LIKE ?))", "%$keywords[$i]%");
                        } else {
                            $select->where("(place.keywords LIKE ? OR category.keywords LIKE ?)", "%$keywords[$i]%");
                        }
                    }
                }
            }
        }

        return $this->_db->fetchAll($select);
    }

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
        if ($values["search_by_distance"] &&
            $values["latitude"] &&
            $values["longitude"]) {
            $formula = Geocoding::getDistanceFormula(
                $values["latitude"],
                $values["longitude"],
                "place",
                "latitude",
                "longitude");
            $searchByDistance = true;
            $sortingType = "distance";
        } else {
            // If we don't have geo, remove distance sorting, fallback on creation date
            if ($sortingType === "distance") {
                $sortingType = "date";
            }
        }

        $select = $this->_db->select()
            ->from(
                ["place" => "job_place"],
                ["*", "time" => "UNIX_TIMESTAMP(place.created_at)", "distance" => $formula]
            )
            ->join(
                ["company" => "job_company"],
                "place.company_id = company.company_id",
                ["company_logo" => "logo", "company_name" => "name", "company_location" => "location"]
            )
            ->join(
                ["job" => "job"],
                "job.job_id = company.job_id"
            )
            ->where("company.is_active = ?", true)
            ->where("place.is_active = ?", true)
            ->where("job.value_id = ?", $valueId)
            ->limit($params["limit"], $params["offset"])
        ;

        // Radius limit
        if ($searchByDistance) {
            /** Distance */
            if (isset($params["radius"]) && $params["radius"] > 0) {
                $select->having("distance < ?", $params["radius"] * 1000);
            }
        }

        // Category filter
        if (array_key_exists("categories", $params) && !empty($params["categories"])) {
            $categories = explode(",", $params["categories"]);
            $select
                ->join(
                    ["category" => "job_category"],
                    "category.category_id = place.category_id",
                    []
                );

            $select->where("category.category_id IN (?)", $categories);
        } else {
            $select
                ->joinLeft(
                    ["category" => "job_category"],
                    "category.category_id = place.category_id",
                    []
                );
        }

        // Keywords filter
        if (array_key_exists("keywords", $values) && !empty($values["keywords"])) {
            $keywords = explode(",", $values["keywords"]);
            if (sizeof($keywords) == 1) {
                $kw = trim($keywords[0]);
                $select->where("(place.keywords LIKE ? OR category.keywords LIKE ?)", "%$kw%");
            } else {
                for($i = 0; $i < sizeof($keywords); $i++) {
                    $kw = trim($keywords[$i]);
                    if($i==0) {
                        $select->find("((place.keywords LIKE ? OR category.keywords LIKE ?)", "%$kw%");
                    } elseif($i == sizeof($keywords)-1) {
                        $select->where("(place.keywords LIKE ? OR category.keywords LIKE ?))", "%$kw%");
                    } else {
                        $select->where("(place.keywords LIKE ? OR category.keywords LIKE ?)", "%$kw%");
                    }
                }
            }
        }

        // Fulltext search
        if (array_key_exists("fulltext", $params)) {
            $fulltext = trim($params["fulltext"]);
            if (!empty($fulltext)) {
                $terms = explode(" ", $fulltext);
                foreach ($terms as $term) {
                    $select
                        ->where("(place.name LIKE ?", "%{$term}%")
                        ->orWhere("place.description LIKE ?", "%{$term}%")
                        ->orWhere("place.email LIKE ?", "%{$term}%")
                        ->orWhere("place.keywords LIKE ?", "%{$term}%")
                        ->orWhere("category.name LIKE ?", "%{$term}%")
                        ->orWhere("category.description LIKE ?", "%{$term}%")
                        ->orWhere("category.keywords LIKE ?", "%{$term}%")
                        ->orWhere("company.name LIKE ?", "%{$term}%")
                        ->orWhere("company.description LIKE ?", "%{$term}%")
                        ->orWhere("company.location LIKE ?", "%{$term}%")
                        ->orWhere("company.email LIKE ?)", "%{$term}%")
                    ;
                }

            }
        }

        switch ($sortingType) {
            case "date":
                $select->order(["place.place_id DESC"]);
                break;
            case "distance":
                $select->order(["distance ASC", "time DESC"]);
                break;
        }

        $select
            ->distinct("place.place_id");

        return $this->toModelClass($this->_db->fetchAll($select));
    }
}
