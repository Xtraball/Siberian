<?php

class Tip_Model_Tip extends Core_Model_Default {

    protected $_is_cacheable = true;

    public function __construct($params = array()) {
        parent::__construct($params);
        return $this;
    }

    /**
     * @return array
     */
    public function getInappStates($value_id) {

        $in_app_states = array(
            array(
                "state" => "tip-view",
                "offline" => true,
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

        $currency = Core_Model_Language::getCurrentCurrency();
        $payload = array(
            "currency_symbol"   => Core_Model_Language::getCurrentCurrency()->getSymbol(),
            "page_title"        => $option_value->getTabbarName(),
            "format"            => $currency->toCurrency(1,array("locale" => $currency->getLocale()))
        );

        return $payload;

    }

    public function getFeaturePaths($option_value) {
        if(!$this->isCacheable()) return array();

        $value_id = $option_value->getId();
        $cache_id = "feature_paths_valueid_{$value_id}";

        if(!$paths = $this->cache->load($cache_id)) {

            $paths = array();

            $params = array(
                'value_id' => $option_value->getId()
            );
            $paths[] = $option_value->getPath("findall", $params, false);

            $this->cache->save($paths, $cache_id, array(
                "feature_paths",
                "feature_paths_valueid_{$value_id}"
            ));
        }

        return $paths;
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

            $dataset = array(
                "option" => $current_option->forYaml(),
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
