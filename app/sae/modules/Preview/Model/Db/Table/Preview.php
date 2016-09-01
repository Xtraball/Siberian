<?php

class Preview_Model_Db_Table_Preview extends Core_Model_Db_Table
{

    protected $_name = "application_option_preview";
    protected $_primary = "preview_id";

    public function findByArray($values) {

        $where = array();
        foreach($values as $field => $value) {
            if(is_array($value))
                $where[] = $this->_db->quoteInto($field.' IN (?)', $value);
            else
                $where[] = $this->_db->quoteInto($field.' = ?', $value);
        }
        $where = join(' AND ', $where);

        $select = $this->select()
            ->from(array('aop' => $this->_name))
            ->join(array('aopl' => $this->_name.'_language'), 'aop.preview_id = aopl.preview_id')
            ->where($where)
            ->setIntegrityCheck(false)
        ;
        return $this->fetchRow($select);

    }

    public function findLibraryIdByLanguageCode($preview_id, $language_code) {
        $select = $this->_db->select()
                    ->from(array("aopl" => $this->_name.'_language'),array("library_id"))
                    ->where("language_code = ?", $language_code)
                    ->where("preview_id = ?", $preview_id)
        ;

        return $this->_db->fetchOne($select);
    }

    public function findAll($values, $params, $order = null) {

        $where = array();
        $limit = null;
        $offset = null;

        if(!empty($values)) {
            foreach($values as $quote => $value) {
                if(is_array($value))
                    $where[] = $this->_db->quoteInto($quote.' IN (?)', $value);
                else
                    $where[] = $this->_db->quoteInto($quote.' = ?', $value);
            }
            $where = join(' AND ', $where);
        }
        if(empty($where)) $where = null;

        if(!empty($params)) {
            $limit = !empty($params['limit']) ? $params['limit'] : null;
            $offset = !empty($params['offset']) ? $params['offset'] : null;
            $group_by = !empty($params['group_by']) ? $params['group_by'] : null;
        }

        $select = $this->select()
            ->from(array('aop' => $this->_name),array("preview_id","option_id"))
            ->joinLeft(array('aopl' => $this->_name.'_language'), 'aop.preview_id = aopl.preview_id', array("language_code","title","description","library_id"))
        ;

        if(!is_null($group_by)) {
            $select->group($group_by);
        }

        if(!is_null($where)) {
            $select->where($where);
        }

        $select->limit($limit,$offset)
            ->setIntegrityCheck(false)
        ;
        return $this->fetchAll($select);
    }

    public function getByOptionId($option_id, $language) {
        $select = $this->select()
            ->from(array('aop' => $this->_name))
            ->join(array('aopl' => $this->_name.'_language'), 'aop.preview_id = aopl.preview_id')
            ->where('aopl.language_code = ?', $language)
            ->where('aop.option_id = ?',$option_id)
        ;
        $select->setIntegrityCheck(false);

        return $this->fetchRow($select);
    }

    public function getById($preview_id, $language) {
        $select = $this->select()
            ->from(array('aop' => $this->_name))
            ->join(array('aopl' => $this->_name.'_language'), 'aop.preview_id = aopl.preview_id')
            ->where('aopl.language_code = ?', $language)
            ->where('aop.preview_id = ?',$preview_id)
        ;
        $select->setIntegrityCheck(false);

        return $this->fetchRow($select);
    }

    public function saveLanguageData($preview_id, $language_data) {
        foreach($language_data as $language_code => $data) {
            $data["preview_id"] = $preview_id;
            $data["description"] = strip_tags($data["description"]);
            $this->_db->delete($this->_name . '_language', array('preview_id = ?' => $preview_id, 'language_code = ?' => $data["language_code"]));
            $this->_db->insert($this->_name . '_language', $data);
        }
    }

    public function findImages($library_id) {
        $select = $this->select()
            ->from(array('ml' => 'media_library'))
            ->join(array('mli' => 'media_library_image'), 'ml.library_id = mli.library_id')
            ->where('ml.library_id = ?', $library_id)
            ->order("mli.position")
            ->setIntegrityCheck(false);
        ;
        return $this->fetchAll($select);
    }

    public function deleteLanguageData($preview_id,$language_code) {
        $this->_db->delete($this->_name . '_language', array('preview_id = ?' => $preview_id, 'language_code = ?' => $language_code));
        return $this;
    }
}