<?php

class Template_Model_Db_Table_Block extends Core_Model_Db_Table {

    protected $_name = "template_block";
    protected $_primary = "block_id";

    public function findAll($values, $order, $params) {

        $fields = $this->getFields();
        $select = $this->select()
            ->from(array('tb' => $this->_name), array())
        ;

        if(!empty($values['app_id'])) {

            $fields['color'] = new Zend_Db_Expr('IFNULL(tba.color, tb.color)');
            $fields['background_color'] =
                new Zend_Db_Expr('IFNULL(tba.background_color, tb.background_color)');
            $fields['border_color'] =
                new Zend_Db_Expr('IFNULL(tba.border_color, tb.border_color)');
            $fields['image_color'] =
                new Zend_Db_Expr('IFNULL(tba.image_color, tb.image_color)');
            $fields['text_opacity'] =
                new Zend_Db_Expr('IFNULL(tba.text_opacity, tb.text_opacity)');
            $fields['background_opacity'] =
                new Zend_Db_Expr('IFNULL(tba.background_opacity, tb.background_opacity)');
            $fields['border_opacity'] =
                new Zend_Db_Expr('IFNULL(tba.border_opacity, tb.border_opacity)');
            $fields['image_opacity'] =
                new Zend_Db_Expr('IFNULL(tba.image_opacity, tb.image_opacity)');

            $join = join(' AND ', array(
                'tba.block_id = tb.block_id',
                $this->_db->quoteInto('tba.app_id = ?', $values['app_id'])
            ));

            $select->joinLeft(array('tba' => 'template_block_app'), $join)
                ->setIntegrityCheck(false)
            ;
            unset($values['app_id']);
        }

        $select->columns($fields);

        if(!empty($values)) {
            foreach($values as $quote => $value) {
                if ($value instanceof Zend_Db_Expr) {
                    $select->where($value);
                } else if (stripos($quote, '?') !== false) {
                    $select->where($quote, $value);
                } else {
                    $select->where($quote . ' = ?', $value);
                }
            }
        }

        if(!empty($params)) {
            if(!empty($params['limit'])) {
                $select->limit($params['limit']);
            }
            if(!empty($params['offset'])) {
                $select->offset($params['offset']);
            }
        }

        if(!empty($order)) {
            $select->order($order);
        }

        $blocks = $this->fetchAll($select);

        $sorted_collection = array();

        foreach($blocks as $block) {
            if(!$block->getParentId()) {
                $sorted_collection[] = $block;
            }
        }

        foreach($blocks as $block) {
            if($block->getParentId()) {
                $this->__buildTree($block, $sorted_collection);
            }
        }

        return $sorted_collection;
    }

    public function saveAppBlock($block) {
        $fields = $this->getFields('template_block_app');
        $data = $block->getData();
        foreach($data as $key => $value) {
            if(!in_array($key, $fields)) unset($data[$key]);
        }

        try {
            $this->_db->insert('template_block_app', $data);
        } catch (Exception $e) {
            if($e->getCode() == 23000) {
                $data["updated_at"] = new Zend_Db_Expr("NOW()");
                $this->_db->update('template_block_app', $data, array('block_id = ?' => $data['block_id'], 'app_id = ?' => $data['app_id']));
            }
        }


    }

    public function findByDesign($design_id) {

        $select = $this->select()
            ->from(array('td' => 'template_design'), array())
            ->join(array('tdb' => 'template_design_block'), 'tdb.design_id = td.design_id', array('block_id', 'color', 'background_color', 'border_color', 'image_color', 'text_opacity', 'background_opacity', 'border_opacity', 'image_opacity'))
            ->join(array('tb' => $this->_name), 'tb.block_id = tdb.block_id', array('name', 'code', 'position', 'created_at', 'updated_at'))
            ->where('td.design_id = ?', $design_id)
            ->setIntegrityCheck(false)
        ;

        return $this->fetchAll($select);
    }

    private function __buildTree($block, $parents = array()) {

        foreach($parents as $parent) {
            if($parent->getId() == $block->getParentId()) {
                $parent->addChild($block);
            }
        }

    }

}
