<?php

use Siberian\Json;

/**
 * Class Rss_Model_Feed
 *
 * @method Rss_Model_Db_Table_Feed getTable()
 */
class Rss_Model_Feed extends Rss_Model_Feed_Abstract
{
    /**
     * @var bool
     */
    protected $_is_cacheable = true;

    /**
     * Rss_Model_Feed constructor.
     * @param array $params
     */
    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->_db_table = 'Rss_Model_Db_Table_Feed';
        return $this;
    }

    /**
     * @param $value_id
     * @return array
     */
    public function getInappStates($value_id)
    {
        $in_app_states = [
            [
                "state" => "rss-list",
                "offline" => true,
                "params" => [
                    "value_id" => $value_id,
                ],
            ],
        ];

        return $in_app_states;
    }

    /**
     * @param $valueId
     * @return string
     */
    public function getLastPosition($valueId)
    {
        $row = $this->getTable()->getLastPosition($valueId);
        if (is_array($row) &&
            array_key_exists("position", $row)) {
            return (integer) $row["position"];
        }
        return 1;
    }

    /**
     * @param $positions
     * @return $this
     * @throws Zend_Db_Adapter_Exception
     */
    public function updatePositions($positions)
    {
        $this->getTable()->updatePositions($positions);
        return $this;
    }

    /**
     * @return array
     */
    public function getNews()
    {

        if ($this->getId() AND empty($this->_news)) {
            $this->_parse();
        }

        return $this->_news;
    }

    /**
     * @param $option
     * @return $this
     */
    public function copyTo($option)
    {
        $this->setId(null)->setValueId($option->getId())->save();
        return $this;
    }

    /**
     * @param $option_value
     * @return array
     */
    public function getFeaturePaths($option_value)
    {
        if (!$this->isCacheable()) return [];

        $action_view = $this->getActionView();

        $value_id = $option_value->getId();
        $cache_id = "feature_paths_valueid_{$value_id}";
        if (!$paths = $this->cache->load($cache_id)) {
            $paths = [];

            $params = [
                'value_id' => $option_value->getId()
            ];
            $paths[] = $option_value->getPath("findall", $params, false);

            if ($uri = $option_value->getMobileViewUri($action_view)) {

                $feeds = $this->getNews();
                foreach ($feeds->getEntries() as $entry) {
                    $feed_id = str_replace("/", "$$", base64_encode($entry->getEntryId()));

                    $params = [
                        "feed_id" => $feed_id,
                        "value_id" => $option_value->getId()
                    ];
                    $paths[] = $option_value->getPath($uri, $params, false);
                }

            }

            $this->cache->save($paths, $cache_id, [
                "feature_paths",
                "feature_paths_valueid_{$value_id}"
            ]);
        }

        return $paths;

    }

    /**
     * @param $option_value
     * @return array
     */
    public function getAssetsPaths($option_value)
    {
        if (!$this->isCacheable()) return [];

        $value_id = $option_value->getId();
        $cache_id = "assets_paths_valueid_{$value_id}";
        if (!$paths = $this->cache->load($cache_id)) {
            $paths = [];

            $feeds = $this->getNews();
            foreach ($feeds->getEntries() as $entry) {
                $picture = $entry->getPicture();
                if (!empty($picture))
                    $paths[] = $picture;

                $matches = [];
                $regex_url = "/((?:http|https)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(?:\/[^\s\"]*)\.(?:png|gif|jpeg|jpg)+)+/";
                preg_match_all($regex_url, $entry->getContent(), $matches);

                $matches = call_user_func_array('array_merge', $matches);

                if ($matches && count($matches) > 1) {
                    unset($matches[0]);
                    $paths = array_merge($paths, $matches);
                }

            }

            $this->cache->save($paths, $cache_id, [
                "assets_paths",
                "assets_paths_valueid_{$value_id}"
            ]);

        }

        return $paths;
    }

    /**
     * @param $option Application_Model_Option_Value
     * @return string
     * @throws Exception
     */
    public function exportAction($option, $export_type = null)
    {
        if ($option && $option->getId()) {

            $current_option = $option;
            $value_id = $current_option->getId();

            $rss_model = new Rss_Model_Feed();
            $rss = $rss_model->find($value_id, "value_id");

            $dataset = [
                "option" => $current_option->forYaml(),
                "rss_feed" => $rss->getData(),
            ];

            try {
                $result = Siberian_Yaml::encode($dataset);
            } catch (Exception $e) {
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
    public function importAction($path)
    {
        $content = file_get_contents($path);

        try {
            $dataset = Siberian_Yaml::decode($content);
        } catch (Exception $e) {
            throw new Exception("#089-04: An error occured while importing YAML dataset '$path'.");
        }

        $application = $this->getApplication();
        $application_option = new Application_Model_Option_Value();

        if (isset($dataset["option"])) {
            $application_option
                ->setData($dataset["option"])
                ->unsData("value_id")
                ->unsData("id")
                ->setData('app_id', $application->getId())
                ->save();

            if (isset($dataset["rss_feed"])) {
                $new_rss = new Rss_Model_Feed();
                $new_rss
                    ->setData($dataset["rss_feed"])
                    ->setData("value_id", $application_option->getId())
                    ->unsData("id")
                    ->unsData("feed_id")
                    ->save();
            }

        } else {
            throw new Exception("#089-02: Missing option, unable to import data.");
        }
    }

    /**
     * GET Feature url for app init
     *
     * @param $optionValue
     * @return array
     */
    public function getAppInitUris ($optionValue)
    {
        try {
            $settings = Json::decode($optionValue->getSettings());
        } catch (\Exception $e) {
            $settings = [];
        }

        // Special feature for places!
        if (array_key_exists("default_page", $settings)) {
            switch ($settings["default_page"]) {
                case "group":
                    $featureUrl = __url("/rss/mobile_feed_group/index", [
                        "value_id" => $this->getValueId()
                    ]);
                    $featurePath = __path("/rss/mobile_feed_group/index", [
                        "value_id" => $this->getValueId()
                    ]);
                    break;
                default:
                    $featureUrl = __url("/rss/mobile_feed_list/index", [
                        "value_id" => $this->getValueId(),
                        "feed_id" => ""
                    ]);
                    $featurePath = __path("/rss/mobile_feed_list/index", [
                        "value_id" => $this->getValueId(),
                        "feed_id" => ""
                    ]);
                    break;
            }
        } else {
            $featureUrl = __url("/rss/mobile_feed_list/index", [
                "value_id" => $this->getValueId()
            ]);
            $featurePath = __path("/rss/mobile_feed_list/index", [
                "value_id" => $this->getValueId()
            ]);
        }

        return [
            "featureUrl" => $featureUrl,
            "featurePath" => $featurePath,
        ];
    }

}
