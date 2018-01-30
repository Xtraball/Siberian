<?php

/**
 * Class Folder2_Form_Settings
 */
class Folder2_Form_Settings extends Siberian_Form_Abstract {

    public function init() {
        parent::init();

        $this
            ->setAction(__path('/folder2/application/editsettings'))
            ->setAttrib('id', 'form-folder-category');

        // Bind as a create form!
        self::addClass('onchange', $this);

        $showSearch = $this->addSimpleCheckbox('show_search', __('Enable search in folders'));

        $cardDesign = $this->addSimpleCheckbox('card_design', __('Use card design'));

        $valueId = $this->addSimpleHidden('value_id');
    }
}