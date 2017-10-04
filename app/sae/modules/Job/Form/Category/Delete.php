<?php
/**
 * Class Job_Form_Category_Delete
 */
class Job_Form_Category_Delete extends Siberian_Form_Abstract {

    public function init() {
        parent::init();

        $this
            ->setAction(__path("/job/category/deletepost"))
            ->setAttrib("id", "form-category-delete")
            ->setConfirmText("You are about to remove this Category ! Are you sure ?");
        ;

        /** Bind as a delete form */
        self::addClass("delete", $this);

        $db = Zend_Db_Table::getDefaultAdapter();
        $select = $db->select()
            ->from('job_category')
            ->where('job_category.category_id = :value')
        ;

        $category_id = $this->addSimpleHidden("category_id", __("Category"));
        $category_id->addValidator("Db_RecordExists", true, $select);
        $category_id->setMinimalDecorator();

        $value_id = $this->addSimpleHidden("value_id");
        $value_id
            ->setRequired(true)
        ;

        $mini_submit = $this->addMiniSubmit();
    }
}