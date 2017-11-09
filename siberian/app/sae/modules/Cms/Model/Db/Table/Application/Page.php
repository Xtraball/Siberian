<?php

class Cms_Model_Db_Table_Application_Page extends Core_Model_Db_Table {

    protected $_name = "cms_application_page";
    protected $_primary = "page_id";
    protected $_modelClass = "Cms_Model_Application_Page";

    public function saveBlock($page_id, $blocks) {
        try {
            $this->beginTransaction();
            $this->_db->delete('cms_application_page_block', array('page_id = ?' => $page_id));
            foreach($blocks as $block) {

                $class = 'Cms_Model_Application_Page_Block_'.ucfirst($block['type']);
                $block_type = new $class();

                if(($block['type'] == 'image' || $block['type'] == 'slider' || $block['type'] == 'cover')) {
                    $this->_db->delete('cms_application_page_block_image_library', array('library_id = ?' => $block["library_id"]));
                    unset($block["library_id"]);
                    $lib_class = 'Cms_Model_Application_Page_Block_Image_Library';
                    $lib = new $lib_class();
                    if(!empty($block["image_url"])) $block["library_id"] = $lib->findLastLibrary();
                }

                $block_type->setData($block);

                if($block_type->isValid()) {

                    $datas = array('block_id' => $block['block_id'], 'page_id' => $page_id, 'position' => $block['position']);
                    $this->_db->insert('cms_application_page_block', $datas);
                    $block_type->setValueId($this->_db->lastInsertId())
                        ->save()
                    ;

                    if(($block['type'] == 'image' || $block['type'] == 'slider' || $block['type'] == 'cover') && !empty($block["image_url"])) {
                        $lib_class = 'Cms_Model_Application_Page_Block_Image_Library';
                        foreach($block["image_url"] as $index => $image_url) {
                            $image_fullsize_url = $block["image_fullsize_url"][$index];
                            $data_image = array(
                                "library_id" => $block["library_id"],
                                "image_url" => $image_url,
                                "image_fullsize_url" => $image_fullsize_url,
                            );
                            $lib = new $lib_class();
                            $lib->addData($data_image)->save();
                        }
                    }

                }

            }
            $this->commit();
        }
        catch(Exception $e) {
            $this->rollback();
        }

    }

}
