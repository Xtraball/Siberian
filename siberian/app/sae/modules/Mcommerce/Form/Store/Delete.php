<?php

namespace Mcommerce\Form\Store;

use Siberian_Form_Abstract as FormAbstract;

/**
 * Class Delete
 * @package Mcommerce\Form\Store
 */
class Delete extends FormAbstract
{
    /**
     * @throws \Zend_Form_Exception
     */
    public function init()
    {
        parent::init();

        $this
            ->setAction(__path('/mcommerce/application_store/delete-post'))
            ->setAttrib('id', 'form-store-delete')
            ->setConfirmText(p__('m_commerce', 'You are about to remove this store! Are you sure?'));

        /** Bind as a delete form */
        self::addClass('delete', $this);

        $storeId = $this->addSimpleHidden('store_id');
        $storeId->setMinimalDecorator();

        $this->addMiniSubmit();
    }
}