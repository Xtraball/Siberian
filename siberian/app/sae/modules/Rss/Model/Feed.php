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
    public function copyTo($option, $parent_id = null)
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
     * @return mixed
     */
    public function _getThumbnail()
    {
        return $this->__getBase64Image($this->getThumbnail());
    }

    /**
     * @param $base64
     * @param $option
     * @return $this
     */
    public function _setThumbnail($base64, $option)
    {
        $thumbnailPath = $this->__setImageFromBase64($base64, $option, 1080, 1920);
        $this->setThumbnail($thumbnailPath);

        return $this;
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

            $rssFeeds = (new Rss_Model_Feed())
                ->findAll(["value_id = ?" => $value_id]);

            $dataSet = [
                "option" => $current_option->forYaml(),
            ];

            $feedSet = [];
            foreach ($rssFeeds as $rssFeed) {
                $data = $rssFeed->getData();
                $data["thumbnail"] = $rssFeed->_getThumbnail();
                $feedSet[] = $data;
            }

            $dataSet["feeds"] = $feedSet;

            try {
                $result = Siberian_Yaml::encode($dataSet);
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
            $dataSet = Siberian_Yaml::decode($content);
        } catch (Exception $e) {
            throw new Exception("#089-04: An error occurred while importing YAML dataset '$path'.");
        }

        $application = $this->getApplication();
        $applicationOption = new Application_Model_Option_Value();

        if (isset($dataSet["option"])) {
            $applicationOption
                ->setData($dataSet["option"])
                ->unsData("value_id")
                ->unsData("id")
                ->setData('app_id', $application->getId())
                ->save();

            if (isset($dataSet["feeds"])) {
                foreach ($dataSet["feeds"] as $feed) {
                    $newRss = new Rss_Model_Feed();
                    $newRss
                        ->setData($feed)
                        ->setData("value_id", $applicationOption->getId())
                        ->unsData("id")
                        ->unsData("feed_id")
                        ->_setThumbnail($feed["thumbnail"], $applicationOption)
                        ->save();
                }
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
        if (array_key_exists("aggregation", $settings)) {
            switch ($settings["aggregation"]) {
                case "split":
                    $featureUrl = __url("/rss/mobile_feed_group/index", [
                        "value_id" => $optionValue->getId()
                    ]);
                    $featurePath = __path("/rss/mobile_feed_group/index", [
                        "value_id" => $optionValue->getId()
                    ]);
                    break;
                case "merge":
                default:
                    $featureUrl = __url("/rss/mobile_feed_list/index", [
                        "value_id" => $optionValue->getId(),
                        "feed_id" => ""
                    ]);
                    $featurePath = __path("/rss/mobile_feed_list/index", [
                        "value_id" => $optionValue->getId(),
                        "feed_id" => ""
                    ]);
                    break;
            }
        } else {
            $featureUrl = __url("/rss/mobile_feed_list/index", [
                "value_id" => $optionValue->getId(),
                "feed_id" => ""
            ]);
            $featurePath = __path("/rss/mobile_feed_list/index", [
                "value_id" => $optionValue->getId(),
                "feed_id" => ""
            ]);
        }

        return [
            "featureUrl" => $featureUrl,
            "featurePath" => $featurePath,
        ];
    }

    /**
     * @param $content
     * @param $stripMedia
     * @return array
     */
    public static function extract ($content, $stripMedia = true)
    {
        $domContent = new Dom_SmartDOMDocument();
        $domContent->loadHTML($content);
        $description = $domContent->documentElement;

        // Just give up if this is empty!
        if (empty($description)) {
            return [
                "media" => null,
                "content" => $content,
            ];
        }

        if ($stripMedia) {
            $images = $description->getElementsByTagName("img");

            $firstImage = null;
            foreach ($images as $image) {
                $srcAttr = $image->getAttribute("src");

                $image->removeAttribute("width");
                $image->removeAttribute("height");

                if (!empty($srcAttr) &&
                    $firstImage === null) {
                    $firstImage = $srcAttr;

                    // Remove extracted image from the content to prevent duplicate!
                    $image->parentNode->removeChild($image);
                }
            }
        }

        $aLinks = $description->getElementsByTagName("a");
        if ($aLinks->length > 0) {
            foreach($aLinks as $aLink) {
                $aLink->setAttribute("target", "_self");
            }
        }

        $content = $domContent->saveHTMLExact();

        return [
            "media" => $firstImage,
            "content" => $content
        ];
    }

}
