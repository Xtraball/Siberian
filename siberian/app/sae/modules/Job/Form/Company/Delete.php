<?php
/**
 * Class Job_Form_Company_Delete
 */
class Job_Form_Company_Delete extends Siberian_Form_Abstract {

    public function init() {
        parent::init();

        $this
            ->setAction(__path("/job/company/deletepost"))
            ->setAttrib("id", "form-company-delete")
            ->setConfirmText("You are about to remove this Company and all the associated Positions ! Are you sure ?");
        ;

        /** Bind as a delete form */
        self::addClass("delete", $this);

        $db = Zend_Db_Table::getDefaultAdapter();
        $select = $db->select()
            ->from('job_company')
            ->where('job_company.company_id = :value')
        ;

        $company_id = $this->addSimpleHidden("company_id", __("Company"));
        $company_id->addValidator("Db_RecordExists", true, $select);
        $company_id->setMinimalDecorator();

        $value_id = $this->addSimpleHidden("value_id");
        $value_id
            ->setRequired(true)
        ;

        $mini_submit = $this->addMiniSubmit();
    }
}