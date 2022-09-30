<?php

/**
 * Class Contact_Model_Contact
 *
 * @method $this setData($key, $value = null)
 * @method $this setLatitude(float $latitude)
 * @method $this setLongitude(float $longitude)
 * @method $this save()
 * @method string getStreet()
 * @method string getPostCode()
 * @method string getCity()
 * @method string getLatitude()
 * @method string getLongitude()
 */
class Contact_Model_Contact extends Core_Model_Default
{

    /**
     * @var array
     */
    public $cache_tags = [
        "feature_contact",
    ];

    /**
     * @var bool
     */
    protected $_is_cacheable = true;

    /**
     * Contact_Model_Contact constructor.
     * @param array $params
     */
    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->_db_table = 'Contact_Model_Db_Table_Contact';
        return $this;
    }

    /**
     * @return string full,none,partial
     */
    public function availableOffline()
    {
        return "partial";
    }

    /**
     * @param $option_value
     * @return bool
     */
    public function getEmbedPayload($option_value = null)
    {

        $payload = false;

        if ($this->getId()) {

            $cover_b64 = null;
            if ($this->getCoverUrl()) {
                $cover_path = path($this->getCoverUrl());
                $image = Siberian_Image::open($cover_path)->cropResize(720);
                $cover_b64 = $image->inline($image->guessType());
            }

            $payload = [
                "contact" => [
                    "name" => $this->getName(),
                    "cover_url" => $cover_b64,
                    "street" => $this->getStreet(),
                    "postcode" => $this->getPostcode(),
                    "city" => $this->getCity(),
                    "address" => str_replace("\n", "<br />", $this->getAddress()),
                    "description" => $this->getDescription(),
                    "phone" => $this->getPhone(),
                    "email" => $this->getEmail(),
                    "form_url" => __path("contact/mobile_form/index", ["value_id" => $option_value->getId()]),
                    "website_url" => $this->getWebsite(),
                    "facebook_url" => $this->getFacebook(),
                    "twitter_url" => $this->getTwitter(),
                    "display_locate_action" => (boolean) $this->getDisplayLocateAction()
                ],
                "card_design" => (boolean) ($this->getDesign() === "card"),
                "page_title" => $option_value->getTabbarName()
            ];

            if ($this->getLatitude() && $this->getLongitude()) {
                $payload['contact']["coordinates"] = [
                    "latitude" => $this->getLatitude(),
                    "longitude" => $this->getLongitude()
                ];
            }

        }

        return $payload;

    }


    /**
     * @return array
     */
    public function getInappStates($value_id)
    {

        $in_app_states = [
            [
                "state" => "contact-view",
                "offline" => true,
                "params" => [
                    "value_id" => $value_id,
                ],
                "childrens" => [
                    [
                        "label" => p__("contact","Form"),
                        "state" => "contact-form",
                        "offline" => true,
                        "params" => [
                            "value_id" => $value_id,
                        ],
                    ],
                    [
                        "label" => p__("contact","Map"),
                        "state" => "contact-map",
                        "offline" => false,
                        "params" => [
                            "value_id" => $value_id,
                        ],
                    ],
                ],
            ],
        ];

        return $in_app_states;
    }

    /**
     * @param $option_value
     * @return array
     */
    public function getFeaturePaths($option_value)
    {
        return [];
    }

    /**
     * @param $option_value
     * @return array
     */
    public function getAssetsPaths($option_value)
    {
        if (!$this->isCacheable()) {
            return [];
        }

        $paths = [];

        $value_id = $option_value->getId();
        $cache_id = "assets_paths_valueid_{$value_id}";
        if (!$result = $this->cache->load($cache_id)) {

            if ($cover = $this->getCoverUrl()) {
                $paths[] = $cover;
            }

            $matches = [];
            $regex_url = "/((?:http|https)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(?:\/[^\s\"]*)\.(?:png|gif|jpeg|jpg)+)+/";
            preg_match_all($regex_url, $this->getDescription(), $matches);

            $matches = call_user_func_array('array_merge', $matches);

            if ($matches && count($matches) > 1) {
                unset($matches[0]);
                $paths = array_merge($paths, $matches);
            }

            $this->cache->save($paths, $cache_id,
                $this->cache_tags + [
                    "assets_paths",
                    "assets_paths_valueid_{$value_id}"
                ]);
        } else {
            $paths = $result;
        }

        return $paths;
    }

    /**
     * @return string
     */
    public function getCoverUrl()
    {
        $cover_path = Application_Model_Application::getImagePath() . $this->getCover();
        $base_cover_path = Application_Model_Application::getBaseImagePath() . $this->getCover();
        if ($this->getCover() && file_exists($base_cover_path)) {
            return $cover_path;
        }
        return '';
    }

    /**
     * @param $option
     * @return $this
     */
    public function copyTo($option, $parent_id = null)
    {

        $this->setId(null)
            ->setValueId($option->getId());

        if ($image_url = $this->getCoverUrl()) {

            $file = pathinfo($image_url);
            $filename = $file['basename'];

            $relativePath = $option->getImagePathTo();
            $folder = Core_Model_Directory::getBasePathTo(Application_Model_Application::PATH_IMAGE . '/' . $relativePath);

            if (!is_dir($folder)) {
                mkdir($folder, 0777, true);
            }

            $img_src = Core_Model_Directory::getBasePathTo($image_url);
            $img_dst = $folder . '/' . $filename;

            if (copy($img_src, $img_dst)) {
                $this->setCover($relativePath . '/' . $filename);
            }
        }

        $this->save();

        return $this;

    }

}
