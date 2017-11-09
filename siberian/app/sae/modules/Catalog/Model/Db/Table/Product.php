<?php

class Catalog_Model_Db_Table_Product extends Core_Model_Db_Table
{
    protected $_name = "catalog_product";
    protected $_primary = "product_id";

    public function findByCategory($category_id, $use_folder, $offset) {

        $select = $this->select()
            ->from(array('cp' => $this->_name))
        ;
        if($use_folder) {
            $select
                ->join(array('cpmc' => 'catalog_product_folder_category'), 'cpmc.product_id = cp.product_id', array())
                ->where('cpmc.category_id = ?', $category_id)
            ;
        }
        else {
            $select->where('cp.category_id = ?', $category_id);
        }

        if(!is_null($offset)) {
            $select->limit(Catalog_Model_Product::DISPLAYED_PER_PAGE, $offset);
        }

        $select->order('cp.position ASC');

        return $this->fetchAll($select);
    }

    public function findByValueId($value_id, $pos_id, $only_active, $with_menus) {

        $select = $this->select()
            ->from(array('cp' => $this->_name))
            ->where('cp.value_id = ?', $value_id)
        ;

        if(!$with_menus) {
            $select->where('cp.type != "menu"');
        }

        if($only_active) {
            $select->where('cp.is_active = 1');
        }

        $select->order('position ASC');

        return $this->fetchAll($select);
    }

    public function findMenus($value_id, $pos_id = null) {

        $where = join(' AND ', array(
            $this->_db->quoteInto('value_id = ?', $value_id),
            'type = "menu"'
        ));

        $select = $this->select()
            ->where($where)
        ;

        return $this->fetchAll($select);

    }

    public function saveCategoryIds($product_id, $category_ids) {

        try {

            $this->beginTransaction();
            $this->_db->delete('catalog_product_folder_category', array('product_id = ?' => $product_id));
            foreach($category_ids as $category_id) {
                $datas = array(
                    'product_id' => $product_id,
                    'category_id' => $category_id
                );
                $this->_db->insert('catalog_product_folder_category', $datas);
            }

            $this->commit();
        } catch (Exception $e) {
            $this->rollback();
            Zend_Debug::dump($e);
            die;
        }

    }

    public function findCategoryIds($product_id) {
        $select = $this->_db->select()
            ->from('catalog_product_folder_category', array('category_id'))
            ->where('product_id = ?', $product_id)
        ;
        return $this->_db->fetchCol($select);
    }

    public function updatePosition($ids) {
    	foreach($ids as $pos => $id) {
    		$this->_db->update($this->_name, array('position' => $pos), array('product_id = ?' => $id));
    	}

    	return $this;
    }

    public function findLastPosition($value_id) {
        $select = $this->select()
            ->from($this->_name, array('position'))
            ->where('value_id = ?', $value_id)
            ->order('position DESC')
            ->limit(1)
        ;

        $pos = $this->_db->fetchOne($select);

        return !empty($pos) ? $pos : 0;

    }

    public function getPosDatas($product_id) {
        $datas = array();
        $datas_tmp = $this->_db->fetchAll($this->_db->select()->from('catalog_product_pos', array('pos_id', 'price'))->where('product_id = ?', $product_id));

        foreach($datas_tmp as $data_tmp) {
            $datas[$data_tmp['pos_id']] = $data_tmp;
        }

        return $datas;
    }

    public function updateOutlets($product_id, array $pos_datas = array()) {

        $this->_db->delete('catalog_product_pos', $this->_db->quoteInto('product_id = ?', $product_id));
        foreach($pos_datas as $data) {
            $price = !empty($data['price']) ? $data['price'] : null;
            if(strpos($price, ',') !== false) $price = str_replace(',', '.', $price);
            $this->_db->insert('catalog_product_pos', array('product_id' => $product_id, 'pos_id' => $data['pos_id'], 'price' => $price));
        }

        return $this;

    }

    public function deleteAllFormats($product_id) {
        $this->_db->delete('catalog_product_format', array('product_id = ?' => $product_id));
        return $this;
    }

    public function getAppIdByProduct() {
        $select = $this->select()
            ->from($this->_name, array('product_id'))
            ->joinLeft('application_option_value',$this->_name.'.value_id = application_option_value.value_id','app_id')
            ->setIntegrityCheck(false)
        ;
        return $this->_db->fetchAssoc($select);
    }
}