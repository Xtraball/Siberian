<?php

class Privacypolicy_Model_Privacypolicy extends Core_Model_Default {

    public $cache_tags = array(
        "feature_privacypolicy",
    );

    /**
     * @var bool
     */
    protected $_is_cacheable = true;

    /**
     * @return array
     */
    public function getInappStates($value_id) {

        $in_app_states = array(
            array(
                "state" => "privacy-policy",
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
     * @return array
     */
    public function getFeaturePaths($option_value) {
        return array();
    }

    /**
     * @param $option_value
     * @return array
     */
    public function getAssetsPaths($option_value) {
        if(!$this->isCacheable()) {
            return array();
        }

        $paths = array();

        $value_id = $option_value->getId();
        $cache_id = "assets_paths_valueid_{$value_id}";
        if(!$result = $this->cache->load($cache_id)) {

            $this->getApplication()->getPrivacyPolicy();
            $privacy_policy = trim($this->getApplication()->getPrivacyPolicy());

            $matches = array();
            $regex_url = "/((?:http|https)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(?:\/[^\s\"]*)\.(?:png|gif|jpeg|jpg)+)+/";
            preg_match_all($regex_url, $privacy_policy, $matches);

            $matches = call_user_func_array('array_merge', $matches);

            if($matches && count($matches) > 1) {
                unset($matches[0]);
                $paths = array_merge($paths, $matches);
            }

            $this->cache->save($paths, $cache_id,
                $this->cache_tags + array(
                    "assets_paths",
                    "assets_paths_valueid_{$value_id}"
                ));
        } else {
            $paths = $result;
        }

        return $paths;

    }
}