<?php

namespace Push2\Form;

use \Siberian_Form_Abstract as FormAbstract;

/**
 * Class Settings
 * @package Push2\Form
 */
class Settings extends FormAbstract
{
    /**
     * @throws \Zend_Form_Exception
     */
    public function init()
    {
        parent::init();

        $this
            ->setAction(__path('/push2/application/edit-settings'))
            ->setAttrib('id', 'push2-form-settings');

        /** Bind as a create form */
        self::addClass('create', $this);

        $this->addSimpleSelect('design', p__('push', 'Design'), [
            'list' => p__('push', 'List'),
            'card' => p__('push', 'Card'),
        ]);

        $valueId = $this->addSimpleHidden('value_id');
        $valueId
            ->setRequired(true);

        $submit = $this->addSubmit(p__('push', 'Save'));
        $submit->addClass('pull-right');
    }
}