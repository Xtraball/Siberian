<?php

/**
 * Class Folder2_Model_Category
 *
 * @method integer getId()
 * @method integer getParentId()
 * @method string getTitle()
 * @method string getSubtitle()
 * @method integer getCategoryId()
 * @method string getTypeId()
 * @method string getPicture()
 * @method string getThumbnail()
 * @method Folder2_Model_Db_Table_Category getTable()
 * @method $this setParentId(integer $parentId)
 * @method $this setPos(integer $position)
 * @method $this setTitle(string $title)
 * @method $this setTypeId(string $type)
 * @method $this setValueId(integer $valueId)
 * @method Folder_Model_Category[] findAll($values = [], $order = null, $params = [])
 *
 * @version 1.0.0
 */
class Folder2_Model_Category extends Core_Model_Default {

    /**
     * @var integer
     */
    public $rootCategoryId;

    /**
     * Folder2_Model_Category constructor.
     * @param array $params
     */
    public function __construct($params = []) {
        parent::__construct($params);
        $this->_db_table = 'Folder2_Model_Db_Table_Category';

        // Default to version 2!
        $this->setVersion(2);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRootCategoryId() {
        if (!$this->rootCategoryId) {
            if ($this->getParentId()) {
                $this->rootCategoryId = $this->getTable()
                    ->findRootCategoryId($this->getParentId());
            } else {
                $this->rootCategoryId = $this->getId();
            }
        }
        return $this->rootCategoryId;
    }

    /**
     * @param $parentId
     * @return int|string
     */
    public function getNextCategoryPosition($parentId) {
        $lastPosition = $this->getTable()
            ->getLastCategoryPosition($parentId);
        if (!$lastPosition) {
            $lastPosition = 0;
        }
        $lastPosition = $lastPosition + 1;

        return $lastPosition;
    }

    /**
     * @param $optionValue
     * @throws Exception
     * @throws \Siberian\Exception
     */
    public function setDefaultImages($optionValue) {
        // Default image pattern!
        $path = '/app/sae/modules/Folder2/resources/design/desktop/flat/images/placeholder/folder-960-600-' . rand(1, 5) . '.png';
        $imagePath = Core_Model_Directory::getBasePathTo($path);
        if (!is_file($imagePath)) {
            $imagePath = Core_Model_Directory::getBasePathTo('/app/sae/modules/Folder2/resources/design/desktop/flat/images/placeholder/folder-960-600-1.png');
        }
        $image = \Gregwar\Image\Image::open($imagePath);
        $image
            ->grayscale()
            ->colorize(rand(-128, 128), rand(-128, 128), rand(-128, 128));

        $pictureFile = Siberian_Feature::createFile($optionValue, '', uniqid() . 'pat.png');
        unlink(Application_Model_Application::getBaseImagePath() . $pictureFile);
        $image->save(Application_Model_Application::getBaseImagePath() . $pictureFile, 'png', 100);
        $thumbnailFile = Siberian_Feature::createFile($optionValue, '', uniqid() . 'pat.png');
        unlink(Application_Model_Application::getBaseImagePath() . $thumbnailFile);
        $image->zoomCrop(512, 512, 0, 0);
        $image->save(Application_Model_Application::getBaseImagePath() . $thumbnailFile, 'png', 100);

        $this
            ->setPicture($pictureFile)
            ->setThumbnail($thumbnailFile)
            ->save();
    }
}
