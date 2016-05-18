<?php

class Topic_Model_Topic extends Core_Model_Default {

    protected $_is_cachable = false;

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Topic_Model_Db_Table_Topic';
        return $this;
    }

    public function prepareFeature($option_value) {

        parent::prepareFeature($option_value);

        if(!$this->getId()) {
            $this->setValueId($option_value->getValueId())->setAppId($option_value->getAppId())->save();
        }

        return $this;
    }

    public function getCategories() {

        if(!$this->getId()) {
            $categories = array();
        } else {
            $category = new Topic_Model_Category();
            $categories = $category->getTopicCategories($this->getId());
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

}
