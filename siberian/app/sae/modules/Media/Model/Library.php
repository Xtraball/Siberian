<?php

use Siberian\Json;

/**
 * Class Media_Model_Library
 */
class Media_Model_Library extends Core_Model_Default
{

    /**
     * @var
     */
    protected $_images;

    /**
     * Media_Model_Library constructor.
     * @param array $params
     */
    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->_db_table = 'Media_Model_Db_Table_Library';
        return $this;
    }

    /**
     * @return array
     */
    public function getImages()
    {

        if (empty($this->_images)) {
            $this->_images = [];
            $image = new Media_Model_Library_Image();
            if ($this->getId()) {
                $this->_images = $image->findAll(['library_id = ?' => $this->getId()], ['position ASC', 'image_id ASC', 'can_be_colorized DESC']);
            }
        }

        return $this->_images;

    }

    /**
     * @return $this
     */
    public function getFirstIcon()
    {
        if (!$this->getId()) {
            return $this;
        }

        $image = new Media_Model_Library_Image();

        $db = $image->getTable();
        $select = $db->select()->where("library_id = ?", $this->getId())->order("image_id ASC");

        $result = $db->fetchRow($select);

        if ($result) {
            return $image->find($result->getId());
        }

        return $this;
    }

    /**
     * @alias $this->getImages();
     */
    public function getIcons()
    {
        return $this->getImages();
    }

    /**
     * @param $new_library_id
     * @param null $option
     */
    public function copyTo($new_library_id, $option = null)
    {

        $images = $this->getImages();
        foreach ($images as $image) {

            $file = pathinfo($image->getLink());
            $filename = $file['basename'];

            $relativePath = $option->getImagePathTo();
            $folder = Core_Model_Directory::getBasePathTo(Application_Model_Application::PATH_IMAGE . $relativePath);

            if (!is_dir($folder)) {
                mkdir($folder, 0777, true);
            }

            $img_src = Core_Model_Directory::getBasePathTo($image->getLink());
            $img_dst = $folder . '/' . $filename;

            if (copy($img_src, $img_dst)) {
                $image->setLink($relativePath . '/' . $filename);
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
    public function getLibraryForDesign($library_id)
    {
        $this->find($library_id);

        $library_name = (design_code() == "flat") ? "{$this->getName()}-flat" : $this->getName();

        $this->find($library_name, "name");

        return $this;
    }

    /**
     * @param null $optionId
     * @return mixed
     * @throws Zend_Exception
     */
    public function getAllFeatureIcons($optionId = null)
    {
        $options = (new Application_Model_Option())->findAll();

        $names = [];
        foreach ($options as $option) {
            $names[] = $option->getData("name");
            $names[] = $option->getData("name") . "-flat";
        }

        /** Icon packs */
        $module = new Installer_Model_Installer_Module();
        $icon_packs = $module->findAll([
            "type = ?" => "icons",
        ]);

        foreach ($icon_packs as $icon_pack) {
            $names[] = $icon_pack->getData('name');
        }

        $libraries = $this->findAll([
            "name IN (?)" => $names
        ]);

        $library_ids = [];
        foreach ($libraries as $library) {
            $library_ids[] = $library->getId();
        }

        $app_id = [];
        if ($this->getApplication()->getId()) {
            $app_id[] = $this->getApplication()->getId();
        }

        $image = new Media_Model_Library_Image();
        $allIcons = $image->findAll([
            'library_id IN (?)' => $library_ids,
            '(app_id IN (?) OR app_id IS NULL)' => $app_id,
            '(option_id = ? OR option_id IS NULL)' => $optionId,
        ], ['position ASC', 'image_id ASC', 'can_be_colorized DESC']);

        return $allIcons;
    }

}
