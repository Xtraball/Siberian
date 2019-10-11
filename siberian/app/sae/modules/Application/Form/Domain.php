<?php

/**
 * Class Application_Form_Domain
 */
class Application_Form_Domain extends Siberian_Form_Abstract
{
    /**
     * @var string
     */
    public $color = 'color-blue';

    /**
     * @throws Zend_Form_Exception
     */
    public function init()
    {
        parent::init();

        $this
            ->setAction(__path("application/settings_domain/save"))
            ->setAttrib("id", "form-settings-domain");

        self::addClass("create", $this);

        // Domain
        $this->addSimpleText(
            "domain",
            p__("application", "Domain name"));

        $submit = $this->addSubmit(__("Save"));
        $submit->addClass("pull-right");
    }
}