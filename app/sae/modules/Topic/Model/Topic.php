<?php

class Topic_Model_Topic extends Core_Model_Default {

    protected $_is_cacheable = true;

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Topic_Model_Db_Table_Topic';
        return $this;
    }

    /**
     * @return array
     */
    public function getInappStates($value_id) {

        $in_app_states = array(
            array(
                "state" => "topic-list",
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

        $payload = array(
            "description"   => $this->getDescription(),
            "collection"    => array(),
            "page_title"    => $option_value->getTabbarName()
        );

        if ($this->getId()) {

            $device_uid = $_GET["device_uid"];

            $subscription = new Topic_Model_Subscription();

            $parent_categories = $this->getCategories();
            foreach ($parent_categories as $category) {
                $picture = $category->getPicture() ? Application_Model_Application::getImagePath() . $category->getPicture() : null;

                $data_category = array(
                    "id"                => $category->getId() * 1,
                    "name"              => $category->getName(),
                    "description"       => $category->getDescription(),
                    "picture"           => $picture,
                    "is_subscribed"     => $device_uid ? $subscription->isSubscribed($category->getId(), $device_uid) : null
                );

                $children = $category->getChildren();
                $data_children = array();
                foreach ($children as $child) {

                    $picture = $child->getPicture() ? Application_Model_Application::getImagePath() . $child->getPicture() : "";

                    $data_children[] = array(
                        "id"                => $child->getId() * 1,
                        "name"              => $child->getName(),
                        "description"       => $child->getDescription(),
                        "picture"           => $picture,
                        "is_subscribed"     => $device_uid ? $subscription->isSubscribed($child->getId(), $device_uid) : null
                    );
                }

                $data_category["children"] = $data_children;

                $payload["collection"][] = $data_category;
            }
        }


        return $payload;

    }

    public function prepareFeature($option_value) {

        parent::prepareFeature($option_value);

        if(!$this->getId()) {
            $this->setValueId($option_value->getValueId())->setAppId($option_value->getAppId())->save();
        }

        return $this;
    }

    public function getFeaturePaths($option_value) {
        if(!$this->isCacheable()) return array();

        $paths = array();

        $paths[] = $option_value->getPath("topic/mobile_list/findall", array(
            "value_id" => $option_value->getId(),
            "device_uid" => "%DEVICE_UID%"
        ));

        return $paths;
    }

    public function getAssetsPaths($option_value) {
        if(!$this->isCacheable()) return array();

        $paths = array();

        $cats = $this->getCategories(true);
        foreach($cats as $cat) {
            $picture = $cat->getPicture() ? Application_Model_Application::getImagePath() . $cat->getPicture() : "";
            if(!empty($picture)) {
                $paths[] = $picture;
            }
        }

        return $paths;
    }

    public function getCategories($all = false) {

        if(!$this->getId()) {
            $categories = array();
        } else {
            $category = new Topic_Model_Category();
            $categories = $category->getTopicCategories($this->getId(), $all);
        }

        return $categories;
    }

    public function copyTo($option) {
        $this->unsTopicId()
            ->unsId()
            ->setValueId($option->getId())
            ->setAppId($option->getAppId())
            ->save()
        ;

        $categories = $option->getObject()->getCategories();
        foreach($categories as $category) {
            $category->unsCategoryId()
                ->unsId()
                ->setTopicId($this->getId())
                ->save()
            ;
        }

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

            $topic_model = new Topic_Model_Topic();
            $topic = $topic_model->find($value_id, "value_id");

            $topic_categories = array();
            if($topic->getId()) {
                $topic_category_model = new Topic_Model_Category();
                $tcs = $topic_category_model->findAll(array(
                    "topic_id = ?" => $topic->getId(),
                ));

                foreach($tcs as $tc) {
                    $topic_categories[] = $tc->getData();
                }
            }

            $dataset = array(
                "option" => $current_option->forYaml(),
                "topic" => $topic->getData(),
                "topic_categories" => $topic_categories,
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

            if(isset($dataset["topic"])) {
                $new_topic = new Topic_Model_Topic();
                $new_topic
                    ->setData($dataset["topic"])
                    ->setData("value_id", $application_option->getId())
                    ->unsData("id")
                    ->unsData("topic_id")
                    ->save()
                ;

                if(isset($dataset["topic_categories"])) {
                    $old_new_ids = array();
                    $topic_categories = array();

                    /** Create the new ones */
                    foreach($dataset["topic_categories"] as $category) {
                        $new_topic_category = new Topic_Model_Category();
                        $new_topic_category
                            ->setData($category)
                            ->setData("topic_id", $new_topic->getId())
                            ->unsData("category_id")
                            ->unsData("id")
                            ->unsData("parent_id")
                            ->save()
                        ;

                        $new_topic_category->setData("parent_id", $category["parent_id"]);
                        $topic_categories[] = $new_topic_category;

                        $old_id = $category["category_id"];
                        $new_id = $new_topic_category->getId();
                        $old_new_ids[$old_id] = $new_id;
                    }

                    /** Re-associate ids */
                    foreach($topic_categories as $topic_category) {
                        $old_parent_id = $topic_category->getData("parent_id");
                        if(isset($old_new_ids[$old_parent_id])) {
                            $topic_category
                                ->setParentId($old_new_ids[$old_parent_id])
                                ->save()
                            ;
                        }
                    }
                }
            }

        } else {
            throw new Exception("#089-02: Missing option, unable to import data.");
        }
    }

}
