<?php

/**
 * Class Places_Form_Search
 */
class Places_Form_Search extends Siberian_Form_Abstract
{
    /**
     * @throws Zend_Form_Exception
     */
    public function init()
    {
        parent::init();

        $this
            ->setAction(__path('/places/application/editpostv2'))
            ->setAttrib("id", "form-cms");

        /** Text */
        $show_text = $this->addSimpleCheckbox("show", __("Fulltext"));
        $show_text->setBelongsTo("search[text]");

        $label_text = $this->addSimpleText("label", __("Label"));
        $label_text->setBelongsTo("search[text]");

        /** Type */
        $show_type = $this->addSimpleCheckbox("show", __("Categories"));
        $show_type->setBelongsTo("search[type]");

        $label_type = $this->addSimpleText("label", __("Label"));
        $label_type->setBelongsTo("search[type]");
    }
}
