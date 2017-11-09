<?php

class Media_Model_Library extends Core_Model_Default {

    protected $_images;

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Media_Model_Db_Table_Library';
        return $this;
    }

    public function getImages() {

        if(empty($this->_images)) {
            $this->_images = array();
            $image = new Media_Model_Library_Image();
            if($this->getId()) {
                $this->_images = $image->findAll(array('library_id = ?' => $this->getId()), array('position ASC', 'image_id ASC', 'can_be_colorized DESC'));
            }
        }

        return $this->_images;

    }

    public function getFirstIcon() {
        if(!$this->getId()) {
            return $this;
        }

        $image = new Media_Model_Library_Image();

        $db = $image->getTable();
        $select = $db->select()->where("library_id = ?", $this->getId())->order("image_id ASC");

        $result = $db->fetchRow($select);

        if($result){
            return $image->find($result->getId());
        }

        return $this;
    }

    /**
     * @alias $this->getImages();
     */
    public function getIcons() {
        return $this->getImages();
    }

    public function copyTo($new_library_id,$option) {

        $images = $this->getImages();
        foreach($images as $image) {

            $file = pathinfo($image->getLink());
            $filename = $file['basename'];

            $relativePath = $option->getImagePathTo();
            $folder = Core_Model_Directory::getBasePathTo(Application_Model_Application::PATH_IMAGE.$relativePath);

            if(!is_dir($folder)) {
                mkdir($folder, 0777, true);
            }

            $img_src = Core_Model_Directory::getBasePathTo($image->getLink());
            $img_dst = $folder.'/'.$filename;

            if(copy($img_src, $img_dst)) {
                $image->setLink($relativePath.'/'.$filename);
            }

            $image->setId(null)
                ->setLibraryId($new_library_id)
                ->save();
        }

    }

    /**
     * Fetch the Library associated with this option, regarding the Design (siberian, flat, ...)
     *
     * @param $library_id
     * @return $this
     */
    public function getLibraryForDesign($library_id) {
        $this->find($library_id);

        $library_name = (design_code() == "flat") ? "{$this->getName()}-flat" : $this->getName();

        $this->find($library_name, "name");

        return $this;
    }

    /**
     * Fetch the Library associated with this option, regarding the Design (siberian, flat, ...)
     *
     * @param $library_id
     * @return $this
     */
    public function getAllFeatureIcons($option_id = null) {
        $options = new Application_Model_Option();
        $options = $options->findAll();

        $names = array();
        foreach($options as $option) {
            $names[] = $option->getName();
            $names[] = $option->getName()."-flat";
        }

        /** Icon packs */
        $module = new Installer_Model_Installer_Module();
        $icon_packs = $module->findAll(array(
            "type = ?" => "icons",
        ));

        foreach($icon_packs as $icon_pack) {
            $names[] = $icon_pack->getData("name");
        }

        $libraries = $this->findAll(array(
            "name IN (?)" => $names
        ));

        $library_ids = array();
        foreach($libraries as $library) {
            $library_ids[] = $library->getId();
        }

        $app_id = array();
        if($this->getApplication()->getId()) {
            $app_id[] = $this->getApplication()->getId();
        }

        $image = new Media_Model_Library_Image();
        $all_icons = $image->findAll(array(
            'library_id IN (?)' => $library_ids,
            '(app_id IN (?) OR app_id IS NULL)' => $app_id,
            '(option_id = ? OR option_id IS NULL)' => $option_id,
        ), array('position ASC', 'image_id ASC', 'can_be_colorized DESC'));

        return $all_icons;
    }

}
