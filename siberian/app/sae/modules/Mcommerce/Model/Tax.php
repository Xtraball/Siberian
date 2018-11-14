<?php

class Mcommerce_Model_Tax extends Core_Model_Default {

    public function __construct($datas = []) {
        parent::__construct($datas);
        $this->_db_table = 'Mcommerce_Model_Db_Table_Tax';
    }

    public function findByStore($store_id) {
        return $this->getTable()->findByStore($store_id);
    }

    public function save() {

        parent::save();

        if($this->getStoreTaxes()) {

            $this->getTable()->beginTransaction();
            try {
                $this->getTable()->saveStoreTaxes($this->getId(), $this->getStoreTaxes());
                $this->getTable()->commit();
            }
            catch(Exception $e) {
                $this->getTable()->rollback();
                throw new Exception($this->_('An error occurred while saving. Please try again later.'));
            }

        }

        return $this;
    }

    public function getExportData($parent = null) {
        $col_to_export = ["tax_id", "name", "rate"];
        $result = [];
        $line_data = [];

        $parent_id = $parent->getId();

        if($parent_id) {

            $taxes = new Mcommerce_Model_Tax();
            $taxes = $taxes->findAll(["mcommerce_id" => $parent_id]);

            if(count($taxes)) {
                $line_data = [];
                $result[] = $col_to_export;
                foreach ($taxes as $tax) {
                    foreach ($tax->getData() as $key => $tax_data) {
                        if(in_array($key, $col_to_export)) {
                            $line_data[] = $tax_data;
                        }
                    }
                    $result[] = $line_data;
                    $line_data = [];
                }

                return $result;
            } else {
                return [];
            }
        } else {
            return [];
        }
    }

}