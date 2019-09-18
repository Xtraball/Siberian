<?php

/**
 * Class Topic_Form_Import
 */
class Topic_Form_Import extends Siberian_Form_Abstract
{

    /**
     * @throws Zend_Form_Exception
     */
    public function init()
    {
        parent::init();

        $this
            ->setAction(__path("/topic/application/import"))
            ->setAttrib("id", "topic-application-import");

        /** Bind as a onchange form */
        self::addClass("create", $this);

        $this->addSimpleFile("filename", __("Import yml"));

        $this->addSimpleHidden("confirm")->setValue(false);
    }
}