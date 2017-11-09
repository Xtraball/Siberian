<?php

class LoyaltyCard_Model_LoyaltyCard extends Core_Model_Default
{

    protected $_action_view = "findall";

    public function __construct($datas = array()) {
        parent::__construct($datas);
        $this->_db_table = 'LoyaltyCard_Model_Db_Table_LoyaltyCard';
    }

    /**
     * @return array
     */
    public function getInappStates($value_id) {

        $in_app_states = array(
            array(
                "state" => "loyaltycard-view",
                "offline" => false,
                "params" => array(
                    "value_id" => $value_id,
                ),
            ),
        );

        return $in_app_states;
    }

    public function findByValueId($value_id) {
        return $this->getTable()->findByValueId($value_id);
    }

    public function findLast($value_id) {
        return $this->getTable()->findLast($value_id);
    }

    public function copyTo($option) {
        $this->setId(null)->setValueId($option->getId())->save();
        return $this;
    }

    public function getAppIdByLoyaltycardId() {
        return $this->getTable()->getAppIdByLoyaltycardId();
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

            $wordpress_model = new Wordpress_Model_Wordpress();
            $wordpress_category_model = new Wordpress_Model_Wordpress_Category();

            $wordpress = $wordpress_model->find($value_id, "value_id");
            $wordpress_data = $wordpress->getData();

            $wordpress_categories = $wordpress_category_model->findAll(array(
                "wp_id = ?" => $wordpress->getId(),
            ));

            $wordpress_categories_data = array();
            foreach($wordpress_categories as $wordpress_category) {
                $wordpress_categories_data[] = $wordpress_category->getData();
            }

            /** Find all wordpress_category */
            $dataset = array(
                "option" => $current_option->forYaml(),
                "wordpress" => $wordpress_data,
                "wordpress_categories" => $wordpress_categories_data,
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

        $wordpress_model = new Wordpress_Model_Wordpress();

        if(isset($dataset["option"]) && isset($dataset["wordpress"])) {
            $new_application_option = $application_option
                ->setData($dataset["option"])
                ->unsData("value_id")
                ->unsData("id")
                ->setData('app_id', $application->getId())
                ->save()
            ;

            $new_value_id = $new_application_option->getId();

            /** Create Job/Options */
            if(isset($dataset["wordpress"]) && $new_value_id) {

                $new_wordpress = $wordpress_model
                    ->setData($dataset["wordpress"])
                    ->unsData("wp_id")
                    ->unsData("id")
                    ->unsData("created_at")
                    ->unsData("updated_at")
                    ->setData("value_id", $new_value_id)
                    ->save()
                ;

                /** Insert wordpress categories */
                if(isset($dataset["wordpress_categories"]) && $new_wordpress->getId()) {

                    foreach($dataset["wordpress_categories"] as $wordpress_category) {

                        $new_wordpress_category = new Wordpress_Model_Wordpress_Category();
                        $new_wordpress_category
                            ->setData($wordpress_category)
                            ->unsData("category_id")
                            ->unsData("id")
                            ->setData("wp_id", $new_wordpress->getId())
                            ->save()
                        ;
                    }

                }

            }

        } else {
            throw new Exception("#088-02: Missing option, unable to import data.");
        }
    }
}