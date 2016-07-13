<?php

class Promotion_Model_Promotion extends Core_Model_Default
{

    public function __construct($datas = array()) {
        parent::__construct($datas);
        $this->_db_table = 'Promotion_Model_Db_Table_Promotion';
    }

    public function getFormattedEndAt() {
        if($this->getData('end_at')) {
            $date = new Zend_Date($this->getData('end_at'));
            return $date->toString($this->_('MM/dd/y'));
        }
    }

    public function hasCondition() {
        return !is_null($this->getConditionType());
    }

    public function resetConditions() {
        $conditions = array('type', 'number_of_points', 'period_number', 'period_type');
        foreach($conditions as $name) {
            $this->setData('condition_'.$name, null);
        }
        return $this;
    }

    public function getPictureUrl() {
        $url = null;
        if($this->getPicture()) {
            if(file_exists(Core_Model_Directory::getBasePathTo(Application_Model_Application::getImagePath().$this->getPicture()))) {
                $url = Application_Model_Application::getImagePath().$this->getPicture();
            }
        }
        return $url;
    }

    public function getUsedPromotions($start_at, $end_at) {
        return $this->getTable()->getUsedPromotions($start_at, $end_at);
    }

    public function save() {
        if($this->getIsIllimited()) $this->setEndDate(null);
        parent::save();
    }

    public function copyTo($option) {
        $this->setId(null)->setValueId($option->getId())->save();
        return $this;
    }

    public function getAppIdByPromotionId() {
        return $this->getTable()->getAppIdByPromotionId();
    }

    public function findAllPromotionsByAppId($app_id) {
        return $this->getTable()->findAllPromotionsByAppId($app_id);
    }

}