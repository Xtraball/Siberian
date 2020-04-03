<?php

/**
 * Class Acl_Model_Db_Table_Acl_Resource
 */
class Acl_Model_Db_Table_Acl_Resource extends Core_Model_Db_Table
{

    /**
     * @var string
     */
    protected $_name = 'acl_resource';
    /**
     * @var string
     */
    protected $_primary = 'resource_id';

    /**
     * @return array
     */
    public function getResourceCodes()
    {
        $select = $this->_db->select()
            ->from($this->_name, ['code']);

        return $this->_db->fetchCol($select);
    }

    /**
     * @param $role_id
     * @return array
     */
    public function findResourcesByRole($role_id)
    {
        $select = $this->_db->select()
            ->from(['ar' => $this->_name], ['code'])
            ->join(['arr' => 'acl_resource_role'], 'ar.resource_id = arr.resource_id', [])
            ->where('arr.role_id = ?', $role_id);

        return $this->_db->fetchCol($select);
    }

    /**
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function findAllParents()
    {
        $select = $this->select()
            ->from(['a' => $this->_name])
            ->where('a.parent_id IS NULL');
        return $this->fetchAll($select);
    }

    /**
     * @param $parent_id
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function findByParentId($parent_id)
    {
        $select = $this->select()
            ->from(['a' => $this->_name])
            ->where('parent_id = ?', $parent_id);
        return $this->fetchAll($select);
    }

    /**
     * @param null $resources
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getUrlByCode($resources = null)
    {
        $select = $this->select()
            ->from(['a' => $this->_name], ['code', 'url'])
            ->where('a.url IS NOT NULL')
            ->where('a.code IN (?)', $resources);
        return $this->fetchAll($select);
    }

    /**
     * @param null $code
     * @return Zend_Db_Table_Row_Abstract|null
     */
    public function findByCode($code = null)
    {
        if ($code) {
            $select = $this->select()
                ->from(['a' => $this->_name])
                ->where('code = ?', $code);
            return $this->fetchRow($select);
        }
        return null;
    }
}