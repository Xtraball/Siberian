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

    /**
     * @param $option_value
     * @return bool
     */
    public function getEmbedPayload($option_value) {

        $payload = array(
            "weblink"       => array(
                "links"         => array(),
                "cover_url"     => null,
            ),
            "page_title"    => $option_value->getTabbarName()
        );

        if($this->getId()) {

            $payload["weblink"]["cover_url"] = null;
            if($this->getCoverUrl()) {
                $picture_file = Core_Model_Directory::getBasePathTo($this->getCoverUrl());
                $payload["weblink"]["cover_url"] = Siberian_Image::open($picture_file)->inline("png");
            }

            foreach($this->getLinks() as $link) {

                $picto_b64 = null;
                if($link->getPictoUrl()) {
                    $picture_file = Core_Model_Directory::getBasePathTo($link->getPictoUrl());
                    $picto_b64 = Siberian_Image::open($picture_file)->inline("png");
                }

                $payload["weblink"]["links"][] = array(
                    "id"                => $link->getId() * 1,
                    "title"             => $link->getTitle(),
                    "picto_url"         => $picto_b64,
                    "url"               => $link->getUrl(),
                    "hide_navbar"       => !!$link->getHideNavbar(),
                    "use_external_app"  => !!$link->getUseExternalApp()
                );
            }

        }

        return $payload;

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

            $class = get_class($this);

            $weblink_model = new $class();
            $weblink = $weblink_model->find($value_id, "value_id");

            $weblink_data = $weblink->getData();
            $weblink_data["cover"] = $weblink->_getCover();

            $weblink_link_model = new Weblink_Model_Weblink_Link();
            $weblink_links = $weblink_link_model->findAll(array(
                "weblink_id = ?" => $weblink->getId(),
            ));

            $weblink_links_data = array();
            foreach($weblink_links as $weblink_link) {
                $weblink_links_data[] = $weblink_link->getData();
            }

            $dataset = array(
                "option" => $current_option->forYaml(),
                "weblink" => $weblink_data,
                "weblink_links" => $weblink_links_data,
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

            $new_value_id = $application_option->getId();

            if(isset($dataset["weblink"])) {
                $new_weblink = new Weblink_Model_Weblink();
                $new_weblink
                    ->setData($dataset["weblink"])
                    ->setData("value_id", $new_value_id)
                    ->unsData("id")
                    ->unsData("weblink_id")
                    ->save();

                $new_weblink_id = $new_weblink->getId();

                if(isset($dataset["weblink_links"])) {
                    foreach($dataset["weblink_links"] as $weblink) {
                        $new_weblink_link = new Weblink_Model_Weblink_Link();
                        $new_weblink_link
                            ->setData($weblink)
                            ->setData("weblink_id", $new_weblink_id)
                            ->unsData("id")
                            ->unsData("link_id")
                            ->save()
                        ;
                    }

                }
            }

        } else {
            throw new Exception("#089-02: Missing option, unable to import data.");
        }
    }
}
