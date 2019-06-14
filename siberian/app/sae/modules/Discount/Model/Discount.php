<?php

use Core\Model\Base;

/**
 * Class Discount_Model_Discount
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.17.0
 */
class Discount_Model_Discount extends Base
{

    /**
     * Discount_Model_Discount constructor.
     * @param array $datas
     * @throws Zend_Exception
     */
    public function __construct($datas = [])
    {
        parent::__construct($datas);
        $this->_db_table = "Discount_Model_Db_Table_Discount";
    }

    /**
     * @return array
     */
    public function getInappStates($value_id)
    {
        $discounts = $this->getTable()->findAll([
            "value_id" => $value_id,
            "is_active" => true
        ], null, null);

        $state_discounts = [];
        foreach ($discounts as $discount) {
            $state_discounts[] = [
                "label" => $discount->getTitle(),
                "state" => "discount-view",
                "offline" => false,
                "params" => [
                    "value_id" => $value_id,
                    "promotion_id" => $discount->getId(),
                ],
            ];
        }

        $in_app_states = [
            [
                "state" => "discount-list",
                "offline" => false,
                "params" => [
                    "value_id" => $value_id,
                ],
                "childrens" => $state_discounts,
            ],
        ];

        return $in_app_states;
    }

    /**
     * @return string
     * @throws Zend_Date_Exception
     */
    public function getFormattedEndAt()
    {
        if ($this->getData('end_at')) {
            $date = new Zend_Date($this->getData('end_at'));
            return $date->toString($this->_('MM/dd/y'));
        }
    }

    /**
     * @return bool
     */
    public function hasCondition()
    {
        return !is_null($this->getConditionType());
    }

    /**
     * @return $this
     */
    public function resetConditions()
    {
        $conditions = ['type', 'number_of_points', 'period_number', 'period_type'];
        foreach ($conditions as $name) {
            $this->setData('condition_' . $name, null);
        }
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPictureUrl()
    {
        $url = null;
        if ($this->getPicture()) {
            if (file_exists(Core_Model_Directory::getBasePathTo(Application_Model_Application::getImagePath() . $this->getPicture()))) {
                $url = Application_Model_Application::getImagePath() . $this->getPicture();
            }
        }
        return $url;
    }

    /**
     * @return string|null
     */
    public function getThumbnailUrl()
    {
        $url = null;
        if ($this->getThumbnail()) {
            if (file_exists(Core_Model_Directory::getBasePathTo(Application_Model_Application::getImagePath() . $this->getThumbnail()))) {
                $url = Application_Model_Application::getImagePath() . $this->getThumbnail();
            }
        }
        return $url;
    }

    /**
     * @param $start_at
     * @param $end_at
     * @return mixed
     */
    public function getUsedPromotions($start_at, $end_at)
    {
        return $this->getTable()->getUsedPromotions($start_at, $end_at);
    }

    /**
     *
     */
    public function save()
    {
        if ($this->getIsIllimited()) $this->setEndDate(null);
        parent::save();
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
     * @return mixed
     */
    public function getAppIdByPromotionId()
    {
        return $this->getTable()->getAppIdByPromotionId();
    }

    /**
     * @param $app_id
     * @return mixed
     */
    public function findAllPromotionsByAppId($app_id)
    {
        return $this->getTable()->findAllPromotionsByAppId($app_id);
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

            $promotion_model = new Promotion_Model_Promotion();

            $promotions = $promotion_model->findAll([
                "value_id = ?" => $value_id,
            ]);

            $promotions_data = [];
            foreach ($promotions as $promotion) {
                $promotions_data[] = $promotion->getData();
            }

            /** Find all wordpress_category */
            $dataset = [
                "option" => $current_option->forYaml(),
                "promotions" => $promotions_data,
            ];

            try {
                $result = Siberian_Yaml::encode($dataset);
            } catch (Exception $e) {
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
    public function importAction($path)
    {
        $content = file_get_contents($path);

        try {
            $dataset = Siberian_Yaml::decode($content);
        } catch (Exception $e) {
            throw new Exception("#088-04: An error occured while importing YAML dataset '$path'.");
        }

        $application = $this->getApplication();
        $application_option = new Application_Model_Option_Value();

        if (isset($dataset["option"])) {
            $new_application_option = $application_option
                ->setData($dataset["option"])
                ->unsData("value_id")
                ->unsData("id")
                ->setData('app_id', $application->getId())
                ->save();

            $new_value_id = $new_application_option->getId();

            /** Create Job/Options */
            if (isset($dataset["promotions"]) && $new_value_id) {

                foreach ($dataset["promotions"] as $promotion) {

                    $new_promotion = new Promotion_Model_Promotion();
                    $new_promotion
                        ->setData($promotion)
                        ->unsData("category_id")
                        ->unsData("id")
                        ->setData("value_id", $new_value_id)
                        ->save();
                }

            }

        } else {
            throw new Exception("#088-02: Missing option, unable to import data.");
        }
    }

}