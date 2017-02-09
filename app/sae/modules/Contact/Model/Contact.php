<?php
class Contact_Model_Contact extends Core_Model_Default {

    protected $_is_cacheable = true;

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Contact_Model_Db_Table_Contact';
        return $this;
    }

    /**
     * @return string full,none,partial
     */
    public function availableOffline() {
        return "partial";
    }

    /**
     * @return array
     */
    public function getInappStates($value_id) {

        $in_app_states = array(
            array(
                "state" => "contact-view",
                "offline" => true,
                "params" => array(
                    "value_id" => $value_id,
                ),
                "childrens" => array(
                    array(
                        "label" => __("Form"),
                        "state" => "contact-form",
                        "offline" => true,
                        "params" => array(
                            "value_id" => $value_id,
                        ),
                    ),
                    array(
                        "label" => __("Map"),
                        "state" => "contact-map",
                        "offline" => false,
                        "params" => array(
                            "value_id" => $value_id,
                        ),
                    ),
                ),
            ),
        );

        return $in_app_states;
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

        if(!$this->isCacheable()) {
            return array();
        }

        $paths = array();
        $paths[] = $option_value->getPath("find", array('value_id' => $option_value->getId()), false);

        return $paths;
    }

    public function getAssetsPaths($option_value) {

        if(!$this->isCacheable()) return array();

        $paths = array();

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
