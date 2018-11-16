<?php

/**
 * Class Places_Form_Design
 */
class Places_Form_Design extends Siberian_Form_Abstract
{
    /**
     * @throws Zend_Form_Exception
     */
    public function init()
    {
        parent::init();

        $this
            ->setAction(__path("/cms/application_page/edit-design"))
            ->setAttrib("id", "form-edit-design");


        $pages = [
            'places' => __("All places"),
            'categories' => __("Categories"),
        ];

        $defaultPage = $this->addSimpleSelect("default_page", __("Default page"), $pages);

        $layout = [
            'place-100' => __("List"),
            'place-50' => __("Two columns"),
            'place-33' => __("Three columns"),
        ];

        $defaultLayout = $this->addSimpleSelect("default_layout", __("Default layout"), $layout);

        $submit = $this->addSubmit(__('Save'), __('Save'));
        $submit->addClass("pull-right");
    }
}