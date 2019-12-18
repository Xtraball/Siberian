<?php

use Siberian\Exception;

use Mcommerce_Model_Tax as Tax;
use Mcommerce\Form\Tax as FormTax;
use Mcommerce\Form\Tax\Delete as FormTaxDelete;

class Mcommerce_Application_Settings_TaxController extends Application_Controller_Default_Ajax
{
    /**
     * @var array
     */
    public $cache_triggers = [
        'edit-post' => [
            'tags' => [
                'homepage_app_#APP_ID#',
            ],
        ],
        'delete-post' => [
            'tags' => [
                'homepage_app_#APP_ID#',
            ],
        ]
    ];

    /**
     *
     */
    public function loadFormAction()
    {
        try {
            $request = $this->getRequest();
            $storeTaxId = $request->getParam('tax_id', null);
            $optionValue = $this->getCurrentOptionValue();

            $tax = (new Tax())->find($storeTaxId);

            if (!$tax->getId()) {
                throw new Exception(p__('m_commerce', "This tax doesn't exists!"));
            }

            $form = new FormTax();
            $form->populate($tax->getData());
            $form->removeNav('nav_tax_form');
            $form->addNav('nav_tax_form_edit', 'Save', false);
            $form->setTaxId($tax->getId());

            $payload = [
                'success' => true,
                'form' => $form->render(),
                'message' => __('Success.'),
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     *
     */
    public function editPostAction()
    {
        try {
            $request = $this->getRequest();
            $values = $request->getPost();

            if (empty($values)) {
                throw new Exception(p__('m_commerce', 'Missing params.'));
            }

            $form = new FormTax();
            if ($form->isValid($values)) {
                /** Do whatever you need when form is valid */
                $tax = new Tax();
                $tax->find($values['tax_id']);
                $tax
                    ->addData($values)
                    ->save();

                $payload = [
                    'success' => true,
                    'message' => __('Success.'),
                ];
            } else {
                /** Do whatever you need when form is not valid */
                $payload = [
                    'error' => true,
                    'message' => $form->getTextErrors(),
                    'errors' => $form->getTextErrors(true),
                ];
            }

        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     * Delete tax
     */
    public function deletePostAction()
    {
        $request = $this->getRequest();
        $values = $request->getPost();

        $form = new FormTaxDelete();
        if ($form->isValid($values)) {
            $tax = new Tax();
            $tax->find($values['tax_id']);
            $tax->delete();

            $payload = [
                'success' => true,
                'message' => p__('m_commerce', 'Tax deleted.'),
            ];
        } else {
            $payload = [
                'error' => true,
                'message' => $form->getTextErrors(),
                'errors' => $form->getTextErrors(true),
            ];
        }

        $this->_sendJson($payload);
    }
}