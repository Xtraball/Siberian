<?php

namespace Fanwall\Form;

use Siberian_Form_Abstract as FormAbstract;
/**
 * Class Settings
 * @package Fanwall\Form
 */
class Settings extends FormAbstract
{
    /**
     * @throws \Zend_Form_Exception
     */
    public function init()
    {
        parent::init();

        $this
            ->setAction(__path("/fanwall/application/edit-settings"))
            ->setAttrib("id", "form-fanwall-settings");
        /** Bind as a create form */
        self::addClass("create", $this);

        $this->addSimpleHidden("fanwall_id");

        $radius = $this->addSimpleText("radius", p__("fanwall","Near me radius (in km)"));
        $radius
            ->setRequired(true);

        $valueId = $this->addSimpleHidden("value_id");
        $valueId
            ->setRequired(true);

        $submit = $this->addSubmit(p__("fanwall", "Save"), p__("fanwall", "Save"));
        $submit->addClass("pull-right");
    }

    /**
     * @param $fanwallId
     */
    public function setFanwallId($fanwallId)
    {
        $this
            ->getElement("fanwall_id")
            ->setValue($fanwallId)
            ->setRequired(true);
    }
}