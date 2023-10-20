<?php

/**
 * Class Core_Model_Db_Table
 */
class Core_Model_Db_Table extends Zend_Db_Table_Abstract
{

    /**
     * @var mixed
     */
    protected $_modelClass;

    /**
     * Core_Model_Db_Table constructor.
     * @param array $options
     */
    public function __construct($options = [])
    {
        if (isset($options["rowClass"])) {
            $rowClass = $options["rowClass"];
        } else {
            $rowClass = "Core_Model_Db_Table_Row";
        }

        parent::__construct(array_merge($options, [
            "db" => "db",
            "rowsetClass" => "Siberian_Db_Table_Rowset",
            "rowClass" => $rowClass
        ]));

        if (isset($options["modelClass"])) {
            $this->_modelClass = $options["modelClass"];
        }
    }

    /**
     *
     */
    public function beginTransaction()
    {
        $this->_db->beginTransaction();
    }

    /**
     *
     */
    public function commit()
    {
        $this->_db->commit();
    }

    /**
     *
     */
    public function rollback()
    {
        $this->_db->commit();
    }

    /**
     * @return mixed
     */
    public function getModelClass()
    {
        return $this->_modelClass;
    }

    /**
     * @return mixed
     */
    public function getPrimaryKey()
    {
        return $this->_primary;
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function insert(array $data)
    {
        return parent::insert($data);
    }

    /**
     * @param array $data
     * @param array|string $where
     * @return int
     */
    public function update(array $data, $where)
    {
        return parent::update($data, $where);
    }

    /**
     * @param array|string $where
     * @return int
     */
    public function delete($where)
    {
        return parent::delete($where);
    }

    /**
     * @param null $tablename
     * @return array
     */
    public function getFields($tablename = null)
    {
        $fields = array_keys($this->_db->describeTable(!is_null($tablename) ? $tablename : $this->_name));
        return array_combine($fields, $fields);
    }

    /**
     * @param $id
     * @return null|Zend_Db_Table_Row_Abstract
     * @throws Exception
     */
    public function findById($id)
    {
        $idIsArray = is_array($id);
        $primaryKey = $this->_getPrimaryKey();

        $where = [];
        foreach ($primaryKey as $pk) {
            $data = $id;
            if ($idIsArray) {
                if (!isset($id[$pk])) throw new Exception('Invalid data for id');
                $data = $id[$pk];
            }
            $where[] = $this->_db->quoteInto("$pk = ?", $data);
        }
        $where = implode_polyfill(' AND ', $where);

        return $this->fetchRow($this->select()->where($where));
    }

    /**
     * @param $value
     * @param $field
     * @return null|Zend_Db_Table_Row_Abstract
     */
    public function findByField($value, $field)
    {
        return $this->fetchRow($this->_db->quoteInto('`' . $field . '` = ?', $value));
    }

    /**
     * @param $values
     * @return null|Zend_Db_Table_Row_Abstract
     */
    public function findByArray($values)
    {
        $where = [];
        foreach ($values as $field => $value) {
            if (is_array($value))
                $where[] = $this->_db->quoteInto($field . ' IN (?)', $value);
            else
                $where[] = $this->_db->quoteInto($field . ' = ?', $value);
        }
        $where = join(' AND ', $where);
        return $this->fetchRow($where);
    }

    /**
     * @param $value
     * @param $field
     * @return null|Zend_Db_Table_Row_Abstract
     * @throws Exception
     */
    public function findLastByField($value, $field)
    {
        $primaryKey = $this->_getPrimaryKey();
        $order = [];

        foreach ($primaryKey as $pk) {
            $order[] = $pk . ' DESC';
        }

        return $this->fetchRow($this->_db->quoteInto('`' . $field . '` = ?', $value), join(', ', $order));
    }

    /**
     * @param $values
     * @return null|Zend_Db_Table_Row_Abstract
     * @throws Exception
     */
    public function findLastByArray($values)
    {
        $where = [];
        $order = [];
        $primaryKey = $this->_getPrimaryKey();

        foreach ($values as $field => $value) {
            if (is_array($value))
                $where[] = $this->_db->quoteInto($field . ' IN (?)', $value);
            else
                $where[] = $this->_db->quoteInto($field . ' = ?', $value);
        }

        if (!empty($where)) {
            $where = join(' AND ', $where);
        }

        foreach ($primaryKey as $pk) {
            $order[] = $pk . ' DESC';
        }

        return $this->fetchRow($where, $order);
    }

    /**
     * @param $values
     * @param $order
     * @param $params
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function findAll($values, $order = null, $params = [])
    {
        $where = [];
        $limit = null;
        $offset = null;

        if (!empty($values)) {
            foreach ($values as $quote => $value) {
                if ($value instanceof Zend_Db_Expr) {
                    $where[] = $value;
                } else if (stripos($quote, '?') !== false) {
                    $where[] = $this->_db->quoteInto($quote, $value);
                } else {
                    $where[] = $this->_db->quoteInto($quote . ' = ?', $value);
                }
            }
        }

        if (empty($where)) {
            $where = null;
        }

        if (!empty($params)) {
            $limit = !empty($params['limit']) ? $params['limit'] : null;
            $offset = !empty($params['offset']) ? $params['offset'] : null;
        }

        return $this->fetchAll($where, $order, $limit, $offset);
    }

    /**
     * @param $values
     * @param $order
     * @param $params
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function findAllWithFilters($values, $order, $params)
    {
        return $this->findAll($values, $order, $params);
    }

    /**
     * @param $select
     * @param $filters
     * @return mixed
     */
    protected function _addFilters($select, $filters)
    {

        if (!empty($filters)) {
            $where = [];
            foreach ($filters as $quote => $value) {
                if (stripos($quote, '?') !== false) $where[] = $this->_db->quoteInto($quote, $value);
                else $where[] = $this->_db->quoteInto($quote . ' = ?', $value);
            }

            if (empty($where)) $where = null;
            else $select->where(join(' AND ', $where));
        }

        return $select;

    }

    /**
     * @param $values
     * @return string
     */
    public function countAll($values)
    {

        $where = [];
        $cols = array_keys($this->_db->describeTable($this->_name));
        $firstCol = current($cols);

        if (!empty($values)) {
            foreach ($values as $quote => $value) {
                if (stripos($quote, '?') !== false) $where[] = $this->_db->quoteInto($quote, $value);
                else $where[] = $this->_db->quoteInto($quote . ' = ?', $value);
            }
        }

        $select = $this->_db->select()
            ->from($this->_name, ['total' => new Zend_Db_Expr("COUNT($firstCol)")]);

        if (!empty($where)) {
            $select->where(join(' AND ', $where));
        }

        return $this->_db->fetchOne($select);
    }

    /**
     * @return bool
     */
    public function checkConnection()
    {
        $this->_db->getConnection();
        return true;
    }

    /**
     * @param $rows
     * @return mixed
     * @throws Zend_Exception
     */
    public function toModelClass($rows)
    {
        $data = [
            'table' => $this,
            'data' => $rows,
            'rowClass' => $this->getRowClass(),
            'stored' => true
        ];

        $rowsetClass = $this->getRowsetClass();

        if (!class_exists($rowsetClass)) {
            require_once 'Zend/Loader.php';
            Zend_Loader::loadClass($rowsetClass);
        }

        return new $rowsetClass($data);
    }

    /**
     * @param null $id
     * @return array|mixed
     * @throws Exception
     */
    protected function _getPrimaryKey($id = null)
    {
        $primaryIsString = (is_string($this->_primary) || is_numeric($this->_primary)) || is_array($this->_primary) && count($this->_primary) == 1;
        $primaryKey = !is_array($this->_primary) ? [$this->_primary] : $this->_primary;

        if ($id != null) {
            if (($primaryIsString && is_array($id) && count($id) > 1) || (!$primaryIsString && count($this->_primary) != count($id))) {
                throw new Exception('Invalid data for id');
            }
        }
        return $primaryKey;
    }

}