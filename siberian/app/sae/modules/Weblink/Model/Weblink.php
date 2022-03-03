<?php

use Siberian_Image as Image;

/**
 * Class Weblink_Model_Weblink
 */
class Weblink_Model_Weblink extends Core_Model_Default
{
    /**
     * @var
     */
    public $_type_id;

    /**
     * @var string
     */
    protected $_db_table = Weblink_Model_Db_Table_Weblink::class;

    /**
     * @return $this
     */
    public function save()
    {
        if (!$this->getId()) {
            $this->setTypeId($this->_type_id);
        }
        parent::save();

        return $this;
    }

    /**
     * @param $optionValue
     * @return array|bool
     */
    public function getEmbedPayload($optionValue = null)
    {
        $payload = [
            'weblink' => [
                'links' => [],
                'cover_url' => null,
            ],
            'page_title' => $optionValue->getTabbarName()
        ];

        if ($this->getId()) {

            $baseUrl = $optionValue->getBaseUrl();
            if (!empty($this->getCoverUrl())) {
                $payload['weblink']['cover_url'] = $baseUrl . $this->getCoverUrl();
            }


            try {
                $settings = \Siberian_Json::decode($optionValue->getSettings());
            } catch (\Exception $exception) {
                $settings = [];
            }

            $payload['settings'] = $settings;

            if ($optionValue->getCode() === 'weblink_multi') {
                foreach ($this->getLinks() as $link) {

                    $picto_b64 = null;
                    if ($link->getPictoUrl()) {
                        $picture_file = path($link->getPictoUrl());
                        $picto_b64 = Image::open($picture_file)->inline('png');
                    }

                    $payload['weblink']['links'][] = [
                        'id' => (integer)$link->getId(),
                        'title' => (string)$link->getTitle(),
                        'picto_url' => (string)$picto_b64,
                        'url' => (string)$link->getUrl(),
                        // pre 4.18.3 options
                        'hide_navbar' => (boolean)$link->getHideNavbar(),
                        'use_external_app' => (boolean)$link->getUseExternalApp(),
                        // post 4.18.3 options
                        'external_browser' => (boolean)$link->getExternalBrowser(),
                        'options' => $link->getOptions()
                    ];
                }
            } else {
                $link = $this->getLink();
                if ($link && $link->getId()) {
                    $payload['link_url'] = (string)$link->getUrl();
                    $payload['external_browser'] = (boolean)$link->getExternalBrowser();
                    $payload['options'] = $link->getOptions();
                }

            }
        }

        return $payload;
    }

    /**
     * @param $id
     * @param null $field
     * @return $this
     */
    public function find($id, $field = null)
    {
        parent::find($id, $field);
        $this->addLinks();
        return $this;
    }

    /**
     *
     */
    public function addLinks()
    {
    }

    /**
     * @param array $values
     * @param null $order
     * @param array $params
     * @return mixed
     */
    public function findAll($values = [], $order = null, $params = [])
    {
        $weblinks = $this->getTable()->findAll($values, $order, $params);
        foreach ($weblinks as $weblink) {
            $weblink->addLinks();
        }
        return $weblinks;
    }

    /**
     * @return mixed
     */
    public function _getCover()
    {
        return $this->__getBase64Image($this->getCover());
    }

    /**
     * @param $base64
     * @param $option
     * @return $this
     */
    public function _setCover($base64, $option)
    {
        $cover_path = $this->__setImageFromBase64($base64, $option, 1080, 1920);
        $this->setCover($cover_path);

        return $this;
    }

    /**
     * @param $option Application_Model_Option_Value
     * @return string
     * @throws Exception
     */
    public function exportAction($option)
    {
        if ($option && $option->getId()) {

            $current_option = $option;
            $value_id = $current_option->getId();

            $class = get_class($this);

            $weblink_model = new $class();
            $weblink = $weblink_model->find($value_id, "value_id");

            $weblink_data = $weblink->getData();
            $weblink_data["cover"] = $weblink->_getCover();

            $weblink_link_model = new Weblink_Model_Weblink_Link();
            $weblink_links = $weblink_link_model->findAll([
                "weblink_id = ?" => $weblink->getId(),
            ]);

            $weblink_links_data = [];
            foreach ($weblink_links as $weblink_link) {
                $weblink_links_data[] = $weblink_link->getData();
            }

            $dataset = [
                "option" => $current_option->forYaml(),
                "weblink" => $weblink_data,
                "weblink_links" => $weblink_links_data,
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

            $new_value_id = $application_option->getId();

            $typeId = $dataset['option']['model'] === 'Weblink_Model_Type_Mono' ? 1 : 2;

            if (array_key_exists('weblink', $dataset)) {
                $new_weblink = new Weblink_Model_Weblink();
                $new_weblink->_type_id = $typeId;
                $new_weblink
                    ->setData($dataset["weblink"])
                    ->setData("value_id", $new_value_id)
                    ->unsData("id")
                    ->unsData("weblink_id")
                    ->save();

                $new_weblink_id = $new_weblink->getId();

                if (array_key_exists('weblink_links', $dataset)) {
                    foreach ($dataset["weblink_links"] as $weblink) {
                        $new_weblink_link = new Weblink_Model_Weblink_Link();
                        $new_weblink_link
                            ->setData($weblink)
                            ->setData("weblink_id", $new_weblink_id)
                            ->unsData("id")
                            ->unsData("link_id")
                            ->save();
                    }
                }
            }

        } else {
            throw new Exception("#089-02: Missing option, unable to import data.");
        }
    }
}
