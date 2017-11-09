<?php
/**
 * Class Job_Form_Company
 */
class Application_Form_Import extends Siberian_Form_Abstract {

    public function init()
    {
        parent::init();

        $this
            ->setAction(__path("/application/customization_features/import"))
            ->setAttrib("id", "form-application-import");

        /** Bind as a onchange form */
        self::addClass("create", $this);

        $this->addSimpleFile("filename", __("Import zip or yml"));

        $this->addSimpleHidden("confirm")->setValue(false);
    }
}