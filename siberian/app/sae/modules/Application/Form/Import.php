<?php
/**
 * Class Application_Form_Import
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

        $this->addSimpleFile("filename", __("Import yml"));

        $this->addSimpleHidden("confirm")->setValue(false);
    }
}