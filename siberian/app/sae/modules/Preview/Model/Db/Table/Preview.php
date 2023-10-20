<?php

/**
 * Class Preview_Model_Db_Table_Preview
 */
class Preview_Model_Db_Table_Preview extends Core_Model_Db_Table
{

    /**
     * @var string
     */
    protected $_name = "application_option_preview";
    /**
     * @var string
     */
    protected $_primary = "preview_id";

    /**
     * @param $values
     * @return Zend_Db_Table_Row_Abstract|null
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

        $select = $this->select()
            ->from(['aop' => $this->_name])
            ->join(['aopl' => $this->_name . '_language'], 'aop.preview_id = aopl.preview_id')
            ->where($where)
            ->setIntegrityCheck(false);
        return $this->fetchRow($select);

    }

    /**
     * @param $preview_id
     * @param $language_code
     * @return string
     */
    public function findLibraryIdByLanguageCode($preview_id, $language_code)
    {
        $select = $this->_db->select()
            ->from(["aopl" => $this->_name . '_language'], ["library_id"])
            ->where("language_code = ?", $language_code)
            ->where("preview_id = ?", $preview_id);

        return $this->_db->fetchOne($select);
    }

    /**
     * @param $values
     * @param null $params
     * @param array $order
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function findAll($values, $params = null, $order = [])
    {

        $where = [];
        $limit = null;
        $offset = null;

        if (!empty($values)) {
            foreach ($values as $quote => $value) {
                if (is_array($value))
                    $where[] = $this->_db->quoteInto($quote . ' IN (?)', $value);
                else
                    $where[] = $this->_db->quoteInto($quote . ' = ?', $value);
            }
            $where = implode_polyfill(' AND ', $where);
        }
        if (empty($where)) {
            $where = null;
        }

        $group_by = null;
        if (!empty($params)) {
            $limit = !empty($params['limit']) ? $params['limit'] : null;
            $offset = !empty($params['offset']) ? $params['offset'] : null;
            $group_by = !empty($params['group_by']) ? $params['group_by'] : null;
        }

        $select = $this->select()
            ->from(['aop' => $this->_name], ['preview_id', 'option_id'])
            ->joinLeft(['aopl' => $this->_name . '_language'], 'aop.preview_id = aopl.preview_id', ["language_code", "title", "description", "library_id"]);

        if ($group_by !== null) {
            $select->group($group_by);
        }

        if ($where !== null) {
            $select->where($where);
        }

        $select->limit($limit, $offset)
            ->setIntegrityCheck(false);
        return $this->fetchAll($select);
    }

    /**
     * @param $option_id
     * @param $language
     * @return Zend_Db_Table_Row_Abstract|null
     */
    public function getByOptionId($option_id, $language)
    {
        $select = $this->select()
            ->from(['aop' => $this->_name])
            ->join(['aopl' => $this->_name . '_language'], 'aop.preview_id = aopl.preview_id')
            ->where('aopl.language_code = ?', $language)
            ->where('aop.option_id = ?', $option_id);
        $select->setIntegrityCheck(false);

        return $this->fetchRow($select);
    }

    /**
     * @param $preview_id
     * @param $language
     * @return Zend_Db_Table_Row_Abstract|null
     */
    public function getById($preview_id, $language)
    {
        $select = $this->select()
            ->from(['aop' => $this->_name])
            ->join(['aopl' => $this->_name . '_language'], 'aop.preview_id = aopl.preview_id')
            ->where('aopl.language_code = ?', $language)
            ->where('aop.preview_id = ?', $preview_id);
        $select->setIntegrityCheck(false);

        return $this->fetchRow($select);
    }

    /**
     * @param $preview_id
     * @param $language_data
     * @throws Zend_Db_Adapter_Exception
     */
    public function saveLanguageData($preview_id, $language_data)
    {
        foreach ($language_data as $language_code => $data) {
            $data["preview_id"] = $preview_id;
            $this->_db->delete($this->_name . '_language', ['preview_id = ?' => $preview_id, 'language_code = ?' => $data["language_code"]]);
            $this->_db->insert($this->_name . '_language', $data);
        }
    }

    /**
     * @param $library_id
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function findImages($library_id)
    {
        $select = $this->select()
            ->from(['ml' => 'media_library'])
            ->join(['mli' => 'media_library_image'], 'ml.library_id = mli.library_id')
            ->where('ml.library_id = ?', $library_id)
            ->order("mli.position")
            ->setIntegrityCheck(false);;
        return $this->fetchAll($select);
    }

    /**
     * @param $preview_id
     * @param $language_code
     * @return $this
     */
    public function deleteLanguageData($preview_id, $language_code)
    {
        $this->_db->delete($this->_name . '_language', ['preview_id = ?' => $preview_id, 'language_code = ?' => $language_code]);
        return $this;
    }
}