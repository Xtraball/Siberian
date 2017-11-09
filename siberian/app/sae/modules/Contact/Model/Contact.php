<?php
class Contact_Model_Contact extends Core_Model_Default {

    /**
     * @var array
     */
    public $cache_tags = array(
        "feature_contact",
    );

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
     * @param $option_value
     * @return bool
     */
    public function getEmbedPayload($option_value) {

        $payload = false;

        if($this->getId()) {

            $cover_b64 = null;
            if($this->getCoverUrl()) {
                $cover_path = Core_Model_Directory::getBasePathTo($this->getCoverUrl());
                $image = Siberian_Image::open($cover_path)->cropResize(720);
                $cover_b64 = $image->inline($image->guessType());
            }

            $payload = array(
                "contact" => array(
                    "name"          => $this->getName(),
                    "cover_url"     => $cover_b64,
                    "street"        => $this->getStreet(),
                    "postcode"      => $this->getPostcode(),
                    "city"          => $this->getCity(),
                    "description"   => $this->getDescription(),
                    "phone"         => $this->getPhone(),
                    "email"         => $this->getEmail(),
                    "form_url"      => __path("contact/mobile_form/index", array("value_id" => $option_value->getId())),
                    "website_url"   => $this->getWebsite(),
                    "facebook_url"  => $this->getFacebook(),
                    "twitter_url"   => $this->getTwitter()
                ),
                "page_title" => $option_value->getTabbarName()
            );

            if($this->getLatitude() && $this->getLongitude()) {
                $payload['contact']["coordinates"] = array(
                    "latitude"      => $this->getLatitude(),
                    "longitude"     => $this->getLongitude()
                );
            }

        }

        return $payload;

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

    /**
     * @param $option_value
     * @return array
     */
    public function getFeaturePaths($option_value) {
        return array();
        /**if(!$this->isCacheable()) {
            return array();
        }

        $value_id = $option_value->getId();
        $cache_id = "feature_paths_valueid_{$value_id}";
        if(!$result = $this->cache->load($cache_id)) {

            $paths = array();
            $paths[] = $option_value->getPath("find", array("value_id" => $option_value->getId()), false);

            $this->cache->save($paths, $cache_id,
                $this->cache_tags + array(
                "feature_paths",
                "feature_paths_valueid_{$value_id}"
            ));
        } else {
            $paths = $result;
        }

        return $paths;*/
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

            if($cover = $this->getCoverUrl()) {
                $paths[] = $cover;
            }

            $matches = array();
            $regex_url = "/((?:http|https)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(?:\/[^\s\"]*)\.(?:png|gif|jpeg|jpg)+)+/";
            preg_match_all($regex_url, $this->getDescription(), $matches);

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

    public function getCoverUrl() {
        $cover_path = Application_Model_Application::getImagePath().$this->getCover();
        $base_cover_path = Application_Model_Application::getBaseImagePath().$this->getCover();
        if($this->getCover() AND file_exists($base_cover_path)) {
            return $cover_path;
        }
        return '';
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
