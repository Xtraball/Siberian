<?php

/**
 * Class Core_Model_Db_Table_Row
 */
class Core_Model_Db_Table_Row extends Zend_Db_Table_Row_Abstract
{

    /**
     * @var
     */
    protected $_modelClass;

    /**
     * @var mixed
     */
    protected $_cols;

    /**
     * Core_Model_Db_Table_Row constructor.
     * @param array $options
     * @throws Zend_Db_Table_Row_Exception
     */
    public function __construct(array $options = array())
    {
        parent::__construct($options);
        try {
            $this->_cols = $this->getTable()->info('cols');
            foreach ($this->_cols as $col) {
                if (!isset($this->_data[$col])) $this->_data[$col] = null;
            }
        } catch (Exception $e) {

        }
    }

    /**
     * @return mixed
     */
    public function getModelClass()
    {
        return $this->_modelClass;
    }

    /**
     * @param $model
     * @return $this
     */
    public function setModelClass($model)
    {
        $this->_modelClass = $model;
        return $this;
    }

    /**
     *
     */
    public function clean()
    {
        $this->_data = array();
        $this->_modifiedFields = array();
    }

    /**
     * @param string $method
     * @param array $args
     * @return $this|array|bool|null|Zend_Db_Table_Row_Abstract|Zend_Db_Table_Rowset_Abstract
     * @throws Zend_Db_Table_Row_Exception
     */
    public function __call($method, array $args)
    {
        switch (substr($method, 0, 3)) {
            case 'get' :
                $key = $this->_transformColumn(substr($method, 3));
                return $this->getData($key);
            case 'set' :
                parent::__set(substr($method, 3), isset($args[0]) ? $args[0] : null);
                return $this;
            case 'uns' :
                $key = $this->_transformColumn(substr($method, 3));
                unset($this->_data[$key]);
                unset($this->_modifiedFields[$key]);
                return $this;
            case 'has' :
                $key = $this->_transformColumn(substr($method, 3));
                return isset($this->_data[$key]);
        }
        return parent::__call($method, $args);
    }

    /**
     * @param null $key
     * @return array|null
     */
    public function getData($key = null)
    {
        if (is_null($key)) return $this->_data;
        elseif (isset($this->_data[$key])) return $this->_data[$key];
        else return null;
    }

    /**
     * @param $key
     * @param null $value
     * @return $this|Core_Model_Db_Table_Row
     * @throws Zend_Db_Table_Row_Exception
     */
    public function setData($key, $value = null)
    {

        if (is_null($value) AND is_array($key)) return $this->mergeData($key);

        parent::__set($key, $value);
        return $this;

    }

    /**
     * @param $datas
     * @return $this
     * @throws Exception
     * @throws Zend_Db_Table_Row_Exception
     */
    public function mergeData($datas)
    {

        foreach ($datas as $name => $value) {
            if ($name == 'id' AND !empty($value)) $this->setId(intval($value));
            if (in_array($name, $this->_cols)) {
                $this->setData($name, $value);
            }
        }

        if ($this->getId() AND !$this->_isExcluded()) {
            $this->_cleanData = $this->_data;
        }

        return $this;
    }

    /**
     * @return array|null|string
     * @throws Zend_Db_Table_Row_Exception
     */
    public function getId()
    {

        if (!is_array($this->_primary)) return null;
        if ($pks = $this->_getPrimaryKey()) {
            if (is_array($pks)) return implode('-', $pks);
            else return $pks;
        }

        return null;
    }

    /**
     * @param $id
     * @return $this|bool
     * @throws Exception
     */
    public function setId($id)
    {

        if (!is_int($id)) return false;
        $primaryIsString = (is_string($this->_primary) || is_numeric($this->_primary)) || is_array($this->_primary) && count($this->_primary) == 1;

        if (($primaryIsString && is_array($id) && count($id) > 1) || (!$primaryIsString && count($this->_primary) != count($id))) {
            throw new Exception('Invalid data for id');
        }

        foreach ($this->_primary as $pk) {
            if (is_array($id) && !isset($id[$pk])) {
                throw new Exception('Invalid data for id');
            }
            $data = is_array($id) ? $id[$pk] : $id;
            $this->_data[$pk] = $data;
        }

        return $this;
    }

    /**
     * @return $this
     * @throws Zend_Db_Table_Row_Exception
     */
    protected function _insert()
    {
        $now = time();
        $dateString = date("Y-m-d H:i:s", $now);
        if (in_array('created_at', $this->_cols) && !$this->getId()) {
            $this->setCreatedAt($dateString);
        }
        if (in_array('updated_at', $this->_cols)) {
            $this->setUpdatedAt($dateString);
        }

        if (in_array('updated_at_utc', $this->_cols)) {
            $this->setUpdatedAtUtc($now);
        }

        if (in_array('created_at_utc', $this->_cols) && !$this->getCreatedAtUtc()) {
            $this->setCreatedAtUtc($now);
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function _update()
    {
        $now = time();
        $dateString = date("Y-m-d H:i:s", $now);

        if (in_array('updated_at', $this->_cols)) {
            $this->setUpdatedAt($dateString);
        }
        if (in_array('updated_at_utc', $this->_cols)) {
            $this->setUpdatedAtUtc($now);
        }

        // Fix created at utc if column is empty
        if (in_array('created_at_utc', $this->_cols) && !$this->getCreatedAtUtc()) {
            $this->setCreatedAtUtc($now);
        }

        return $this;
    }

    /**
     * @return bool
     */
    protected function _isExcluded()
    {
        return in_array($this->_table->getModelClass(), array(
            'Media_Model_Gallery_Video_Youtube',
            'Media_Model_Gallery_Image_Picasa',
            'Media_Model_Gallery_Video_Itunes',
            'Media_Model_Gallery_Video_Vimeo',
            'Media_Model_Gallery_Image_Instagram',
            'Cms_Model_Application_Page_Block_Video_Podcast',
            'Cms_Model_Application_Page_Block_Video_Youtube',
            'Cms_Model_Application_Page_Block_Video_Link',
//            'Admin_Model_Notification',
        ));
    }

    /**
     * @param string $key
     * @return null|string|string[]
     */
    protected function _transformColumn($key)
    {
        $key = preg_replace('/(?!^)[[:upper:]]/', ' \0', $key);
        $key = strtolower(str_replace(' ', '_', $key));
        return $key;
    }

}
