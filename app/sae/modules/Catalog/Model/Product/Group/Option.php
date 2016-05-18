<?php

class Catalog_Model_Product_Group_Option extends Core_Model_Default {

    public function __construct($datas = array()) {
        parent::__construct($datas);
        $this->_db_table = 'Catalog_Model_Db_Table_Product_Group_Option';
    }

    public function getExportData($parent = null) {
        $col_to_export = array("option_id", "name");
        $groups = new Catalog_Model_Product_Group();
        $groups = $groups->findAll(array("app_id" => $this->getApplication()->getAppId()));

        if(count($groups)) {
            $result = array();
            $csv_result = array();
            $heading = array();
            $line_data = array();
            foreach ($groups as $group) {
                $group_options = new Catalog_Model_Product_Group_Option();
                $group_options = $group_options->findAll(array("group_id" => $group->getGroupId()));
                if(count($group_options)) {
                    foreach ($group_options as $group_option) {
                        foreach($group_option->getData() as $key => $data) {
                            if(in_array($key, $col_to_export)) {
                                $line_data[] = $data;
                            }
                        }
                        $result[] = $line_data;
                        $line_data = array();
                    }
                }
            }

            if(count($result)) {
                $csv_result[] = $col_to_export;

                foreach($result as $data_to_insert) {
                    $csv_result[] = $data_to_insert;
                }

            } else {
                $csv_result = array();
            }
        } else {
            $csv_result = array();
        }

        return $csv_result;
    }
}