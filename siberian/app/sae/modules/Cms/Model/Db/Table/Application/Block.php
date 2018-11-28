<?php

/**
 * Class Cms_Model_Db_Table_Application_Block
 */
class Cms_Model_Db_Table_Application_Block extends Core_Model_Db_Table
{

    /**
     * @var string
     */
    protected $_name = "cms_application_block";
    /**
     * @var string
     */
    protected $_primary = "block_id";


    /**
     * @param $page_id
     * @return array
     */
    public function findByPage($page_id)
    {

        // Récupère les sous-tables
        $subtables = $this->_db->fetchCol($this->_db->select()->from('cms_application_block', ['type']));
        $blocks = [];
        foreach ($subtables as $subtable) {
            $select = $this->select()
                ->from(['cap' => 'cms_application_page'], [])
                ->join(['capb' => 'cms_application_page_block'], 'capb.page_id = cap.page_id', ['position'])
                ->join(['cab' => $this->_name], 'cab.block_id = capb.block_id', ['block_id', 'type', 'template', 'mobile_template'])
                ->join([$subtable => 'cms_application_page_block_' . $subtable], "$subtable.value_id = capb.value_id")
                ->where('cap.page_id = ?', $page_id)
                ->order('capb.position ASC')
                ->setIntegrityCheck(false);

            foreach ($this->fetchAll($select) as $block) {
                $blocks[$block->getPosition()] = $block;
            }
        }

        ksort($blocks);

        return $blocks;

    }
}