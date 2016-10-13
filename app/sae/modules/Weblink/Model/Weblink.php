<?php
class Weblink_Model_Weblink extends Core_Model_Default {

    protected $_type_id;

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Weblink_Model_Db_Table_Weblink';
        return $this;
    }

    public function save() {

        if(!$this->getId()) $this->setTypeId($this->_type_id);
        parent::save();

        return $this;
    }

    public function find($id, $field = null) {
        parent::find($id, $field);
        $this->addLinks();
        return $this;
    }

    public function findAll($values = array(), $order = null, $params = array()) {
        $weblinks = $this->getTable()->findAll($values, $order, $params);
        foreach($weblinks as $weblink) {
            $weblink->addLinks();
        }
        return $weblinks;
    }

    /**
     * @param bool $base64
     * @return string
     */
    public function _getCover() {
        return $this->__getBase64Image($this->getCover());
    }

    /**
     * @param $base64
     * @param $option
     * @return $this
     */
    public function _setCover($base64, $option) {
        $cover_path = $this->__setImageFromBase64($base64, $option, 1080, 1920);
        $this->setCover($cover_path);

        return $this;
    }

    /**
     * @param $option Application_Model_Option_Value
     * @return string
     * @throws Exception
     */
    public function exportAction($option) {
        if($option && $option->getId()) {

            $current_option = $option;
            $value_id = $current_option->getId();

            $weblink_model = new Weblink_Model_Weblink();
            $weblink = $weblink_model->find($value_id, "value_id");

            $weblink_data = $weblink->getData();
            $weblink_data["cover"] = $weblink->_getCover();

            $dataset = array(
                "option" => $current_option->forYaml(),
                "weblink" => $weblink_data,
            );

            try {
                $result = Siberian_Yaml::encode($dataset);
            } catch(Exception $e) {
                throw new Exception("#089-03: An error occured while exporting dataset to YAML.");
            }

            return $result;

        } else {
            throw new Exception("#089-01: Unable to export the feature, non-existing id.");
        }
    }

    /**
     * @param $path
     * @throws Exception
     */
    public function importAction($path) {
        $content = file_get_contents($path);

        try {
            $dataset = Siberian_Yaml::decode($content);
        } catch(Exception $e) {
            throw new Exception("#089-04: An error occured while importing YAML dataset '$path'.");
        }

        $application = $this->getApplication();
        $application_option = new Application_Model_Option_Value();

        if(isset($dataset["option"])) {
            $application_option
                ->setData($dataset["option"])
                ->unsData("value_id")
                ->unsData("id")
                ->setData('app_id', $application->getId())
                ->save()
            ;

        } else {
            throw new Exception("#089-02: Missing option, unable to import data.");
        }
    }
}
