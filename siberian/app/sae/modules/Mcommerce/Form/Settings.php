<?php

namespace Mcommerce\Form;

use Siberian_Form_Abstract as FormAbstract;

/**
 * Class Mcommerce_Form_Store
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
            ->setAction(__path('/mcommerce/application_store/edit-settings'))
            ->setAttrib('id', 'settings_edit_form')
            ->addNav('settings-nav');

        /** Bind as a create form */
        self::addClass('create', $this);
    }
}