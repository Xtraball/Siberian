<?php

use Siberian\Exception;

use Mcommerce_Model_Store as Store;
use Mcommerce\Form\Store as FormStore;
use Mcommerce\Form\Store\Delete as FormStoreDelete;

class Mcommerce_Application_StoreController extends Application_Controller_Default_Ajax
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

            $store = (new Store())->find($storeTaxId);

            if (!$store->getId()) {
                throw new Exception(p__('m_commerce', "This store doesn't exists!"));
            }

            $form = new FormStore();
            $form->populate($store->getData());
            $form->removeNav('nav_store_form');
            $form->addNav('nav_store_form_edit', 'Save', false);
            $form->setStoreId($store->getId());

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

            $form = new FormStore();
            if ($form->isValid($values)) {
                /** Do whatever you need when form is valid */
                $store = new Store();
                $store->find($values['store_id']);
                $store
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

        $form = new FormStoreDelete();
        if ($form->isValid($values)) {
            $store = new Store();
            $store->find($values['tax_id']);
            $store->delete();

            $payload = [
                'success' => true,
                'message' => p__('m_commerce', 'Store deleted.'),
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