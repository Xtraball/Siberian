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
            ->setAction(__path("/topic/application/import-user"))
            ->setAttrib("id", "topic-application-import");

        /** Bind as a onchange form */
        self::addClass("create", $this);

        $this->addSimpleFile("filename", p__("topic", "Import CSV, JSON, YAML"));

        $this->addSimpleHidden("value_id");
    }
}