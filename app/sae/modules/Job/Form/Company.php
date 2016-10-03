<?php
/**
 * Class Job_Form_Company
 */
class Job_Form_Company extends Siberian_Form_Abstract {

    public function init() {
        parent::init();

        $this
            ->setAction(__path("/job/company/editpost"))
            ->setAttrib("id", "form-company")
            ->setAttrib("autocomplete", "off")
            ->addNav("job-company-nav")
        ;

        /** Bind as a create form */
        self::addClass("create", $this);

        $this->addSimpleHidden("company_id");

        $name = $this->addSimpleText("name", __("Name"));
        $name
            ->setRequired(true)
        ;

        $description = $this->addSimpleTextarea("description", __("Description"));
        $description
            ->setRequired(true)
            ->setNewDesignLarge()
            ->setRichtext()
        ;

        $website = $this->addSimpleText("website", __("Website"));

        $email = $this->addSimpleText("email", __("E-mail"));
        $email
            ->addValidator("EmailAddress")
            ->setRequired(true)
            ->setAttrib("autocomplete", "job-company-email")
        ;

        $customer = new Customer_Model_Customer();
        $customers = $customer->findAll(array(
            "app_id = ?" => Application_Model_Application::getApplication()->getId(),
        ));

        $_values = array();
        foreach($customers as $customer) {
            $_values[$customer->getId()] = sprintf("%s %s <%s>", $customer->getFirstname(), $customer->getLastname(), $customer->getEmail());
        }

        $admins = $this->addSimpleMultiSelect("administrators", __("Administrators"), $_values);

        $address = $this->addSimpleText("location", __("Address"));
        $address
            ->setRequired(true)
        ;

        $employees = $this->addSimpleText("employee_count", __("Employee count"));
        
        $logo = $this->addSimpleImage("logo", __("Logo"), __("Import a logo"), array("width" => 500, "height" => 500, "required" => true));
        $header = $this->addSimpleImage("header", __("Header"), __("Import a header"), array("width" => 1200, "height" => 400, "required" => true));

        $options = array(
            "global" => __("Use global configuration"),
            "hidden" => __("Hidden"),
            "contactform" => __("Contact form"),
            "email" => __("Email"),
            "both" => __("Email & Contact form"),
        );

        $display_contact = $this->addSimpleSelect("display_contact", __("Display contact"), $options);

        $job_id = $this->addSimpleHidden("job_id");
        $job_id
            ->setRequired(true)
        ;

        $value_id = $this->addSimpleHidden("value_id");
        $value_id
            ->setRequired(true)
        ;
    }

    public function setCompanyId($company_id) {
        $this->getElement("company_id")->setValue($company_id)->setRequired(true);
    }
}