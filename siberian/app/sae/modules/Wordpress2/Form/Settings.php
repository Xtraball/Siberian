<?php

/**
 * Class Wordpress2_Form_Settings
 */
class Wordpress2_Form_Settings extends Siberian_Form_Abstract {

    public function init() {
        parent::init();

        $this
            ->setAction(__path('/wordpress2/application/editsettings'))
            ->setAttrib('id', 'form-wordpress2-category');

        // Bind as a create form!
        self::addClass('onchange', $this);

        $showSearch = $this->addSimpleCheckbox('group_queries', __('Group all queries into a single list'));

        $cardDesign = $this->addSimpleCheckbox('card_design', __('Use card design'));

        $valueId = $this->addSimpleHidden('value_id');
    }
}