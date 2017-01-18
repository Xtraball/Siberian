<?php

class Core_Model_Db_Table extends Zend_Db_Table_Abstract
{

    protected $_modelClass;

    public function __construct($options = array()) {
        if(isset($options['rowClass'])) $rowClass = $options['rowClass'];
        else $rowClass = 'Core_Model_Db_Table_Row';

        parent::__construct(array_merge($options, array('db' => 'db', 'rowsetClass' => 'Siberian_Db_Table_Rowset', 'rowClass' => $rowClass)));

        if(isset($options['modelClass'])) $this->_modelClass = $options['modelClass'];

    }

    public function beginTransaction() {
        $this->_db->beginTransaction();
    }

    public function commit() {
        $this->_db->commit();
    }

    public function rollback() {
        $this->_db->commit();
    }

    public function getModelClass() {
        return $this->_modelClass;
    }

    public function getPrimaryKey() {
        return $this->_primary;
    }

    public function insert(array $data) {
        return parent::insert($data);
    }

    public function update(array $data, $where) {
        return parent::update($data, $where);
    }

    public function delete($where) {
        return parent::delete($where);
    }

    public function getFields($tablename = null) {
        $fields = array_keys($this->_db->describeTable(!is_null($tablename) ? $tablename : $this->_name));
        return array_combine($fields, $fields);
    }

    public function findById($id) {
        $idIsArray = is_array($id);
        $primaryKey = $this->_getPrimaryKey();

        $where = array();
        foreach($primaryKey as $pk) {
            $data = $id;
            if($idIsArray) {
                if(!isset($id[$pk])) throw new Exception('Invalid data for id');
                $data = $id[$pk];
            }
            $where[] = $this->_db->quoteInto("$pk = ?", $data);
        }
        $where = implode(' AND ', $where);

        return $this->fetchRow($this->select()->where($where));
    }

    public function findByField($value, $field) {
        return $this->fetchRow($this->_db->quoteInto('`'.$field.'` = ?', $value));
    }

    public function findByArray($values) {
        $where = array();
        foreach($values as $field => $value) {
            if(is_array($value))
                $where[] = $this->_db->quoteInto($field.' IN (?)', $value);
            else
                $where[] = $this->_db->quoteInto($field.' = ?', $value);
        }
        $where = join(' AND ', $where);
        return $this->fetchRow($where);
    }

    public function findLastByField($value, $field) {
        $primaryKey = $this->_getPrimaryKey();
        $order = array();

        foreach($primaryKey as $pk) {
            $order[] = $pk . ' DESC';
        }

        return $this->fetchRow($this->_db->quoteInto('`'.$field.'` = ?', $value), join(', ', $order));
    }

    public function findLastByArray($values) {
        $where = array();
        $order = array();
        $primaryKey = $this->_getPrimaryKey();

        foreach($values as $field => $value) {
            if(is_array($value))
                $where[] = $this->_db->quoteInto($field.' IN (?)', $value);
            else
                $where[] = $this->_db->quoteInto($field.' = ?', $value);
        }

        if(!empty($where)) {
            $where = join(' AND ', $where);
        }

        foreach($primaryKey as $pk) {
            $order[] = $pk . ' DESC';
        }
        $order = join(', ', $order);

        return $this->fetchRow($where, $order);
    }

    public function findAll($values, $order, $params) {

        $where = array();
        $limit = null;
        $offset = null;

        if(!empty($values)) {
            foreach($values as $quote => $value) {
                if($value instanceof Zend_Db_Expr) $where[] = $value;
                else if(stripos($quote, '?') !== false) $where[] = $this->_db->quoteInto($quote, $value);
                else $where[] = $this->_db->quoteInto($quote . ' = ?', $value);
            }
        }
        if(empty($where)) $where = null;

        if(!empty($params)) {
            $limit = !empty($params['limit']) ? $params['limit'] : null;
            $offset = !empty($params['offset']) ? $params['offset'] : null;
        }

        return $this->fetchAll($where, $order, $limit, $offset);
    }

    public function findAllWithFilters($values, $order, $params) {
        return $this->findAll($values, $order, $params);
    }

    protected function _addFilters($select, $filters) {

        if(!empty($filters)) {
            $where = array();
            foreach($filters as $quote => $value) {
                if(stripos($quote, '?') !== false) $where[] = $this->_db->quoteInto($quote, $value);
                else $where[] = $this->_db->quoteInto($quote . ' = ?', $value);
            }

            if(empty($where)) $where = null;
            else $select->where(join(' AND ', $where));
        }

        return $select;

    }

    public function countAll($values) {

        $where = array();
        $cols = array_keys($this->_db->describeTable($this->_name));
        $firstCol = current($cols);

        if(!empty($values)) {
            foreach($values as $quote => $value) {
                if(stripos($quote, '?') !== false) $where[] = $this->_db->quoteInto($quote, $value);
                else $where[] = $this->_db->quoteInto($quote . ' = ?', $value);
            }
        }

        $select = $this->_db->select()
            ->from($this->_name, array('total' => new Zend_Db_Expr("COUNT($firstCol)")))
        ;

        if(!empty($where)) {
            $select->where(join(' AND ', $where));
        }

        return $this->_db->fetchOne($select);
    }

    public function checkConnection() {
        $this->_db->getConnection();
        return true;
    }

    /**
     * Converts a simple select to a rowset
     *
     * @param $rows
     * @return mixed
     */
    public function toModelClass($rows) {
        $data  = array(
            'table'    => $this,
            'data'     => $rows,
            'rowClass' => $this->getRowClass(),
            'stored'   => true
        );

        $rowsetClass = $this->getRowsetClass();

        if (!class_exists($rowsetClass)) {
            require_once 'Zend/Loader.php';
            Zend_Loader::loadClass($rowsetClass);
        }

        return new $rowsetClass($data);
    }

    protected function _getPrimaryKey($id = null) {
        $primaryIsString = (is_string($this->_primary) || is_numeric($this->_primary)) || is_array($this->_primary) && count($this->_primary) == 1;
        $primaryKey = !is_array($this->_primary) ? array($this->_primary) : $this->_primary;

        if($id != null) {
            if(($primaryIsString && is_array($id) && count($id) > 1) || (!$primaryIsString && count($this->_primary) != count($id))) {
                throw new Exception('Invalid data for id');
            }
        }
        return $primaryKey;
    }

}