<?php

/**
 * Class Places_Form_Category
 */
class Places_Form_Category extends Siberian_Form_Abstract
{
    /**
     * @throws Zend_Form_Exception
     */
    public function init()
    {
        parent::init();

        $this
            ->setAction(__path("/cms/application_page/edit-category"))
            ->setAttrib("id", "form-edit-category");

        $this->addNav('nav-categories', __('Save'));

        $title = $this->addSimpleText('title', __("Name"));
        $title->setRequired(true);

        $description = $this->addSimpleTextarea('subtitle', __("Description"));
        $description->setRichtext();

        $this->addSimpleImage('picture', __('Add an image'), __('Add an image'), [
            'width' => 512,
            'height' => 512,
        ]);
    }
}