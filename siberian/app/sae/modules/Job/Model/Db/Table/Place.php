<?php

class Job_Model_Db_Table_Place extends Core_Model_Db_Table {

    protected $_name = "job_place";
    protected $_primary = "place_id";

    /**
     * @param $values
     * @param $order
     * @param $params
     * @return array
     */
    public function findActive($values, $order, $params) {
        $more_search = $values["more_search"];
        $formula = new Zend_Db_Expr("0");
        if($values["search_by_distance"] && $values["latitude"] && $values["longitude"]) {
            $formula = Siberian_Google_Geocoding::getDistanceFormula($values["latitude"], $values["longitude"], "place", $lat_name = 'latitude', $long_name = 'longitude');
            $search_by_distance = true;
        }

        $select = $this->_db->select()
            ->from(array("place" => "job_place"), array("*", "time" => "UNIX_TIMESTAMP(place.created_at)", "distance" => $formula))
            ->join(array("company" => "job_company"), "place.company_id = company.company_id", array("company_logo" => "logo", "company_name" => "name", "company_location" => "location"))
            ->join(array("job" => "job"), "job.job_id = company.job_id")
            ->joinLeft(array("category" => "job_category"), "category.category_id = place.category_id", array())
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
            $select->order(array("distance ASC", "time DESC"));
        }

        if(!$values["position"]) {
            $select->order(array("time DESC"));
        }

        if($more_search) {
            if(isset($values["categories"]) && is_array($values["categories"])) {
                $ids = array();
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
}
