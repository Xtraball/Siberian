<?php
/**
 * @see Zend_Db_Table_Rowset_Abstract
 */
require_once 'Zend/Db/Table/Rowset/Abstract.php';

class Siberian_Db_Table_Rowset extends Zend_Db_Table_Rowset_Abstract {

    public function findById($id) {
        foreach($this->_data as $position => $row) {
            $row = $this->_loadAndReturnRow($position);
            if($row->getId() == $id) return $row;
        }
        $modelClass = $this->_table->getModelClass();
        return new $modelClass();
    }

    public function addRow($position, $object) {
        if($position < 0) $position = $this->_count;
        $this->_data[$position] = $object->getData();
        $this->_rows[$position] = $object;
        $this->_count = count($this->_data);
        return $this;
    }

    public function removeRow($position) {
        if(isset($this->_data[$position])) unset($this->_data[$position]);
        if(isset($this->_rows[$position])) unset($this->_rows[$position]);
        $this->_count = count($this->_data);
        return $this;
    }

    public function removeCurrent() {
        if($this->valid()) {
            $key = $this->key();

            if(!empty($this->_data[$key])) {
                unset($this->_data[$key]);
                $this->_data = array_values($this->_data);
            }

            if(!empty($this->_rows[$key])) {
                unset($this->_rows[$key]);
                $this->_rows = array_values($this->_rows);
            }
            $this->_count--;
        }

        return $this;
    }

    protected function _loadAndReturnRow($position) {
        if (!isset($this->_data[$position])) {
            require_once 'Zend/Db/Table/Rowset/Exception.php';
            throw new Zend_Db_Table_Rowset_Exception("Data for provided position ". $position . " does not exist");
        }

        // do we already have a row object for this position?
        if (empty($this->_rows[$position])) {

            $row = new $this->_rowClass(array('data' => $this->_data[$position], 'table' => $this->_table));

            $modelClass = $this->_table->getModelClass();
            $model = new $modelClass();

            try {
                $model->setData($row->getData())->setId($row->getData($model->getTable()->getPrimaryKey()));
            }
            catch(Exception $e) {
                $model->setData($row->getData())->setId($row->getId());
            }

            $this->_rows[$position] = $model;

        }

        // return the row object
        return $this->_rows[$position];
    }

}
