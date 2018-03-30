<?php

/**
 * Class Wordpress2_Form_Query
 */
class Wordpress2_Form_Query extends Siberian_Form_Abstract {

    public function init() {
        parent::init();

        $this
            ->setAction(__path('/wordpress2/application/editquery'))
            ->setAttrib('id', 'form-wordpress2-query');

        self::addClass('create', $this);

        $title = $this->addSimpleText('title', __('Title'));
        $subtitle = $this->addSimpleText('subtitle', __('Subtitle'));

        $this->addSimpleMultiCheckbox('categories', __('Categories'), []);

        $this->addSimpleHidden('wordpress2_id');
        $valueId = $this->addSimpleHidden('value_id');

        $this->addSubmit(__('Save'))
            ->addClass('default_button')
            ->addClass('pull-right')
            ->addClass('submit_button');
    }
}