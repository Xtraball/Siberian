<?php

/**
 * Class Folder2_Form_Category
 */
class Folder2_Form_Category extends Siberian_Form_Abstract {

    public function init() {
        parent::init();

        $this
            ->setAction(__path('/folder2/application/editpost'))
            ->setAttrib('id', 'form-folder-category');

        // Bind as a create form!
        self::addClass('create', $this);
        self::addClass('callback', $this);
        self::addClass('folderForm', $this);

        $this->addSimpleHtml('form_header', '<p></p>');

        $title = $this->addSimpleText('title', __('Title'));
        $title->setRequired(true);

        $subtitle = $this->addSimpleText('subtitle', __('Subtitle'));

        $picture = $this->addSimpleImage(
            'picture',
            __('Cover'),
            __('Import a cover image'),
            [
                'width' => 960,
                'height' => 600
            ],
            [],
            true);
        $picture
            ->addClass('default_button')
            ->addClass('form_button');

        $thumbnail = $this->addSimpleImage(
            'thumbnail',
            __('Thumbnail'),
            __('Import a thumbnail image'),
            [
                'width' => 512,
                'height' => 512
            ],
            [],
            true);
        $thumbnail
            ->addClass('default_button')
            ->addClass('form_button');

        $categoryId = $this->addSimpleHidden('category_id');
        $valueId = $this->addSimpleHidden('value_id');

        $this->addSubmit(__('Save'))
            ->addClass('default_button')
            ->addClass('pull-right')
            ->addClass('submit_button');
    }
}