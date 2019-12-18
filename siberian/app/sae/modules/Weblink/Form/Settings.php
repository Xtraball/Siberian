<?php

/**
 * Class Weblink_Form_Settings
 */
class Weblink_Form_Settings extends Siberian_Form_Abstract
{
    /**
     * @throws Zend_Form_Exception
     */
    public function init()
    {
        parent::init();

        $this
            ->setAction(__path('/weblink/application/edit-settings'))
            ->setAttrib('id', 'form-edit-settings');

        self::addClass('create', $this);

        $this->addSimpleHidden('value_id');

        $this->addSimpleCheckbox('showSearch', p__('weblink', 'Enable search'));
        $this->addSimpleSelect('cardDesign', p__('weblink', 'Page design'), [
            '0' => p__('weblink', 'List'),
            '1' => p__('weblink', 'Card'),
        ]);

        $this->addSimpleImage(
            'cover',
            p__('weblink', 'Cover image'),
            p__('weblink', 'Cover image'), [
            'width' => 1000,
            'height' => 600,
        ]);

        $submit = $this->addSubmit(p__('weblink', 'Save'), 'save');
        $submit->addClass('pull-right');
    }
}