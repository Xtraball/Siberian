<?php

class Preview_Model_Preview extends Core_Model_Default
{
    protected $_language_data = array();

    const IMAGE_PATH = "/images/previews";


    public function __construct($datas = array()) {
        parent::__construct($datas);
        $this->_db_table = 'Preview_Model_Db_Table_Preview';
    }

    public function save() {
        parent::save();

        if($this->_language_data) {
            $this->getTable()->saveLanguageData($this->getId(),$this->_language_data);
        }

        return $this;
    }

    public function setLanguageData($data) {
        $this->_language_data = $data;
        return $this;
    }

    public function findImages() {
        return $this->getTable()->findImages($this->getLibraryId());
    }

    public function findLibraryIdByLanguageCode($language_code) {
        return $this->getTable()->findLibraryIdByLanguageCode($this->getId(), $language_code);
    }

    public function deleteLanguageData($language_code) {
        return $this->getTable()->deleteLanguageData($this->getId(),$language_code);
    }

    public function deleteTranslation($language_code) {
        $library_id = $this->findLibraryIdByLanguageCode($language_code);
        $this->setLibraryId($library_id);
        $images = $this->findImages();

        foreach($images as $image) {
            if(!unlink(Core_Model_Directory::getBasePathTo($image->getLink()))) {
                throw new Exception($this->_("Unable to delete the file %s",$image->getLink()));
            }
        }

        $this->deleteLanguageData($language_code);
    }

}
