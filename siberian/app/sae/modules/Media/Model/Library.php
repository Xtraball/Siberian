<?php

/**
 * Class Media_Model_Library
 *
 * @method integer getId()
 * @method Media_Model_Db_Table_Library_Image getTable()
 */
class Media_Model_Library extends Core_Model_Default
{

    /**
     * @var
     */
    protected $_images;

    /**
     * @var string
     */
    protected $_db_table = Media_Model_Db_Table_Library::class;

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
     * @return Media_Model_Library_Image|Zend_Db_Table_Row_Abstract
     * @throws Zend_Db_Select_Exception
     * @throws Zend_Db_Statement_Exception
     * @throws Zend_Db_Table_Exception
     * @throws Zend_Exception
     */
    public function getFirstIcon()
    {
        if (!$this->getId()) {
            throw new Exception('This library does not exists!');
        }

        $images = (new Media_Model_Library_Image())
            ->findAll(
                ['library_id' => $this->getId()],
                ['image_id ASC']
            );
        foreach ($images as $image) {
            $link = $image->getData('link');
            if (stripos($link, '/app') === false) {
                $link = '/images/library' . $link;
            }

            $apath = path($link);
            if (is_file($apath) === false) {
                // Else delete the record!
                $image->delete();
                // Then jump on next!
                continue;
            }

            // Or send the working image!
            return $image;
        }

        throw new Exception('This library has no image!');
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
            $folder = path(Application_Model_Application::PATH_IMAGE . $relativePath);

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
     * @param $libraryId
     * @return $this
     */
    public function getLibraryForDesign($libraryId)
    {
        $this->find($libraryId);
        $libraryName = $this->getName();
        // We add `-flat` prefix only if it is not present!
        if (stripos($libraryName, '-flat') === false) {
            $this->find($libraryName . '-flat', 'name');
        }

        return $this;
    }

    /**
     * @param null $optionId
     * @param true $withInactive
     * @return mixed
     * @throws Zend_Exception
     */
    public function getAllFeatureIcons($optionId = null, $withInactive = true)
    {
        $options = (new Application_Model_Option())->findAll();

        $where = [];

        $names = [
            'icons-home',
        ];
        foreach ($options as $option) {
            $names[] = $option->getData("name");
            $names[] = $option->getData("name") . "-flat";
        }

        /** Icon packs */
        $module = new Installer_Model_Installer_Module();
        $icon_packs = $module->findAll([
            'type IN (?)' => ['icons', 'layout', 'template'],
        ]);

        foreach ($icon_packs as $icon_pack) {
            $names[] = $icon_pack->getData('name');
        }

        $libraries = $this->findAll([
            'name IN (?)' => $names
        ]);

        $library_ids = [];
        foreach ($libraries as $library) {
            $library_ids[] = $library->getId();
        }
        $where['library_id IN (?)'] = $library_ids;

        $app_id = [];
        if ($this->getApplication() && $this->getApplication()->getId()) {
            $app_id[] = $this->getApplication()->getId();

            $where['(app_id IN (?) OR app_id IS NULL)'] = $app_id;
        }

        $where['(option_id = ? OR option_id IS NULL)'] = $optionId;

        if ($withInactive === false) {
            $where['is_active = ?'] = 1;
        }

        $image = new Media_Model_Library_Image();

        return $image->findAll($where, ['image_id DESC']);
    }

}
