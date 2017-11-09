<?php
class Radio_Model_Radio extends Core_Model_Default {

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Radio_Model_Db_Table_Radio';
        return $this;
    }

    /**
     * @return array
     */
    public function getInappStates($value_id) {

        $in_app_states = array(
            array(
                "state" => "radio",
                "offline" => false,
                "params" => array(
                    "value_id" => $value_id,
                ),
            ),
        );

        return $in_app_states;
    }

    /**
     * @param $option_value
     * @return bool
     */
    public function getEmbedPayload($option_value) {

        $payload = false;

        if($this->getId()) {

            // Fix for shoutcast, force stream!
            $contentType = Siberian_Request::testStream($this->getLink());
            if(!in_array(explode('/', $contentType)[0], ['audio']) &&
                !in_array($contentType, ['application/ogg'])) {
                if(strrpos($this->getLink(), ';') === false) {
                    $this->setLink($this->getLink() . '/;');
                }
            }

            $payload = array(
                "radio" => array(
                    "url"           => addslashes($this->getLink()),
                    "title"         => $this->getTitle(),
                    "background"    => $option_value->getBaseUrl() . "/images/application" . $this->getBackground(),
                )
            );

        }

        return $payload;

    }

    public function copyTo($option) {
        $this->setId(null)->setValueId($option->getId())->save();
        return $this;
    }

    /**
     * @param bool $base64
     * @return string
     */
    public function _getBackground() {
        return $this->__getBase64Image($this->getBackground());
    }

    /**
     * @param $base64
     * @param $option
     * @return $this
     */
    public function _setBackground($base64, $option) {
        $background_path = $this->__setImageFromBase64($base64, $option, 1080, 1920);
        $this->setBackground($background_path);

        return $this;
    }

    /**
     * @param $option Application_Model_Option_Value
     * @return string
     * @throws Exception
     */
    public function exportAction($option, $export_type = null) {
        if($option && $option->getId()) {

            $current_option = $option;
            $value_id = $current_option->getId();

            $radio_model = new Radio_Model_Radio();

            $radio = $radio_model->find($value_id, "value_id");
            $radio_data = $radio->getData();
            $radio_data["background"] = $radio->_getBackground();

            $dataset = array(
                "option" => $current_option->forYaml(),
                "radio" => $radio_data,
            );

            try {
                $result = Siberian_Yaml::encode($dataset);
            } catch(Exception $e) {
                throw new Exception("#088-03: An error occured while exporting dataset to YAML.");
            }

            return $result;

        } else {
            throw new Exception("#088-01: Unable to export the feature, non-existing id.");
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
            throw new Exception("#088-04: An error occured while importing YAML dataset '$path'.");
        }

        $application = $this->getApplication();

        $application_option = new Application_Model_Option_Value();
        $radio_model = new Radio_Model_Radio();

        if(isset($dataset["option"]) && isset($dataset["radio"])) {
            $new_application_option = $application_option
                ->setData($dataset["option"])
                ->unsData("value_id")
                ->unsData("id")
                ->setData('app_id', $application->getId())
                ->save()
            ;

            $new_value_id = $new_application_option->getId();

            /** Create Job/Options */
            if($new_value_id) {
                $new_radio = $radio_model
                    ->setData($dataset["radio"])
                    ->unsData("radio_id")
                    ->unsData("id")
                    ->unsData("created_at")
                    ->unsData("updated_at")
                    ->setData("value_id", $new_value_id)
                    ->_setBackground($dataset["radio"]["background"], $new_application_option)
                    ->save()
                ;
            } else {
                /** Log, empty feature/default */
            }

        } else {
            throw new Exception("#088-02: Missing option, unable to import data.");
        }
    }

}
