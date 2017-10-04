<?php
/**
 * Class Job_Form_Category_Toggle
 */
class Job_Form_Category_Toggle extends Siberian_Form_Abstract {

    public function init() {
        parent::init();

        $this
            ->setAction(__path("/job/category/togglepost"))
            ->setAttrib("id", "form-category-toggle")
        ;

        /** Bind as a delete form */
        self::addClass("toggle", $this);

        $db = Zend_Db_Table::getDefaultAdapter();
        $select = $db->select()
            ->from('job_category')
            ->where('job_category.category_id = :value')
        ;

        $place_id = $this->addSimpleHidden("category_id", __("Category"));
        $place_id->addValidator("Db_RecordExists", true, $select);
        $place_id->setMinimalDecorator();

        $value_id = $this->addSimpleHidden("value_id");
        $value_id
            ->setRequired(true)
        ;

        $this->addMiniSubmit(null, "<i class='fa fa-power-off icon icon-power-off'></i>", "<i class='fa fa-check icon icon-ok'></i>");

        $this->defaultToggle($this->mini_submit, "Enable category", "Disable category");
    }
}