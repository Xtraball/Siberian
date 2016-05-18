<?php
class Contact_Model_Contact extends Core_Model_Default {

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Contact_Model_Db_Table_Contact';
        return $this;
    }

    public function getCoverUrl() {
        $cover_path = Application_Model_Application::getImagePath().$this->getCover();
        $base_cover_path = Application_Model_Application::getBaseImagePath().$this->getCover();
        if($this->getCover() AND file_exists($base_cover_path)) {
            return $cover_path;
        }
        return '';
    }

    public function getFeaturePaths($option_value) {

        if(!$this->isCachable()) return array();

        $action_view = $this->getActionView();

        $paths = array();
        $paths[] = $option_value->getPath("find", array('value_id' => $option_value->getId()), false);

        if($cover = $this->getCoverUrl()) {
            $paths[] = $cover;
        }

        return $paths;
    }

    public function copyTo($option) {

        $this->setId(null)
            ->setValueId($option->getId())
        ;

        if($image_url = $this->getCoverUrl()) {

            $file = pathinfo($image_url);
            $filename = $file['basename'];

            $relativePath = $option->getImagePathTo();
            $folder = Core_Model_Directory::getBasePathTo(Application_Model_Application::PATH_IMAGE.'/'.$relativePath);

            if(!is_dir($folder)) {
                mkdir($folder, 0777, true);
            }

            $img_src = Core_Model_Directory::getBasePathTo($image_url);
            $img_dst = $folder.'/'.$filename;

            if(copy($img_src, $img_dst)) {
                $this->setCover($relativePath.'/'.$filename);
            }
        }

        $this->save();

        return $this;

    }

}
