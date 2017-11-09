<?php

class Application_Model_Db_Table_Option_Value extends Core_Model_Db_Table {

    protected $_name = "application_option_value";
    protected $_primary = "value_id";
    protected $_modelClass = "Application_Model_Option_Value";

    public function findAll($values, $order, $params) {

        $option_fields = array_keys($this->_db->describeTable('application_option'));
        $option_fields = array_combine($option_fields, $option_fields);
        unset($option_fields['icon_id']);
        unset($option_fields['position']);

        $option_value_fields = $this->getFields();

        $option_value_fields['tabbar_name'] = new Zend_Db_Expr('IFNULL(aov.tabbar_name, ao.name)');
        $option_value_fields['layout_id'] = new Zend_Db_Expr('IFNULL(aov.layout_id, "1")');

        $option_value_fields['background_image'] = new Zend_Db_Expr('IF(a.use_homepage_background_image_in_subpages, a.background_image, IFNULL(aov.background_image, ""))');
        $option_value_fields['background_image_hd'] = new Zend_Db_Expr('IF(a.use_homepage_background_image_in_subpages, a.background_image_hd, IFNULL(aov.background_image, ""))');
        $option_value_fields['background_image_tablet'] = new Zend_Db_Expr('IF(a.use_homepage_background_image_in_subpages, a.background_image_tablet, IFNULL(aov.background_image, ""))');

        $option_value_fields['use_homepage_background_image'] = new Zend_Db_Expr("a.use_homepage_background_image_in_subpages");
        $option_value_fields['has_background_image'] = new Zend_Db_Expr('IF(aov.background_image IS NOT NULL, 1, 0)');
        $option_value_fields['icon_id'] = new Zend_Db_Expr('IFNULL(aov.icon_id, ao.icon_id)');

        $select = $this->select()
            ->from(array('aov' => $this->_name), $option_value_fields)
            ->join(array('ao' => 'application_option'), 'ao.option_id = aov.option_id', $option_fields)
            ->join(array('a' => 'application'), 'a.app_id = aov.app_id', array())
            ->joinLeft(array('lv' => 'padlock_value'), 'lv.app_id = a.app_id AND lv.value_id = aov.value_id', array('is_locked' => new Zend_Db_Expr('IF(lv.value_id IS NULL, NULL, 1)')))
        ;

        if(!empty($values)) {

            $fields_to_prefix = array("app_id", "is_active");
            foreach($fields_to_prefix as $field_to_prefix) {
                if (!empty($values[$field_to_prefix])) {
                    $values["aov.".$field_to_prefix] = $values[$field_to_prefix];
                    unset($values[$field_to_prefix]);
                }
            }

            foreach($values as $quote => $value) {
                if($value instanceof Zend_Db_Expr) $select->where($value);
                else if(stripos($quote, '?') !== false) $select->where($this->_db->quoteInto($quote, $value));
                else $where[] = $select->where($this->_db->quoteInto($quote . ' = ?', $value));
            }
        }

        if(!empty($order)) $select->order($order);
        else $select->order('aov.folder_category_position ASC')->order('aov.position ASC')->order('ao.position ASC');

        if(!empty($params)) {
            if(!empty($params['limit'])) $select->limit($params['limit']);
            if(!empty($params['offset'])) $select->offset($params['offset']);
        }

        $select->setIntegrityCheck(false);

        $rows = $this->fetchAll($select);

        foreach($rows as $row) {
            $row->prepareUri();
        }

        return $rows;

    }

    /**
     * @param $value_id
     * @param $app_id
     * @return bool
     */
    public function valueIdBelongsTo($value_id, $app_id) {
        $result = $this->_db->fetchOne(
            "SELECT COUNT(value_id) FROM {$this->_name} WHERE value_id = ? AND app_id = ?",
            array($value_id, $app_id)
        );

        return ($result > 0);
    }

    public function getLastPosition($app_id = null) {

        $select = $this->select()->from($this->_name, array('position'))
            ->order('position DESC')
            ->limit(1)
        ;
        if($app_id) {
            $select->where('app_id = ?', $app_id);
        }

        $position = $this->_db->fetchOne($select);

        return $position ? $position : 0;

    }

    public function getLastFolderCategoryPosition($category_id) {

        $select = $this->select()->from($this->_name, array('folder_category_position'))
            ->where('folder_category_id = ?', $category_id)
            ->order('folder_category_position DESC')
            ->limit(1)
        ;

        $position = $this->_db->fetchOne($select);

        return $position ? $position : 0;

    }

    public function getFolderValues($app_id, $option_id) {
        $select = $this->select()
            ->from(array('aov' => 'application_option_value'))
            ->where('aov.app_id = ?', $app_id)
            ->where('aov.option_id = ?', $option_id)
            ->setIntegrityCheck(false)
        ;

        return $this->fetchAll($select);
    }

    public function findLibraryId($option_id) {
        $select = $this->_db->select()
            ->from(array('ao' => 'application_option'), array('library_id'))
            ->where('ao.option_id = ?', $option_id)
        ;

        return $this->_db->fetchOne($select);
    }

    public function getOptionDatas($option_id) {
        $fields = array_keys($this->_db->describeTable('application_option'));
        $fields = array_combine($fields, $fields);
        $fields['tabbar_name'] = new Zend_Db_Expr('name');
        $select = $this->_db->select()
            ->from(array('ao' => 'application_option'), $fields)
            ->where('ao.option_id = ?', $option_id)
        ;
        return $this->_db->fetchRow($select);
    }

    public function findAllWithOptionsInfos($values = array(), $order = null, $params = array()) {
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

        $select = $this->select()
        ->setIntegrityCheck(false)
        ->from(array('a' => 'application_option_value'), array("*",
            "tabbar_name" => new Zend_Db_Expr('IFNULL(a.tabbar_name, ao.name)')))
        ->join(array('ao' => 'application_option'), 'a.option_id = ao.option_id');

        if($where) {
            foreach ($where as $where_cond) {
                $select = $select->where($where_cond);
            }
        }

        if($order) {
            $select = $select->order($order);
        }

        if($limit) {
            $select = $select->limit($limit, $offset);
        }

        return $this->fetchAll($select);
    }

    public function getFeaturesByApplication() {
        $field = array('value_id', 'app_id');
        $select = $this->select()
            ->from($this->_name, $field);
        $result = array();
        foreach($this->_db->fetchAll($select) as $row) {
            $result[$row['value_id']] = $row['app_id'];
        }
        return $result;
    }
}
