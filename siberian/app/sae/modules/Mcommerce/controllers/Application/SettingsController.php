<?php

use Siberian\Exception;

use Mcommerce_Model_Mcommerce as Mcommerce;
use Mcommerce\Form\Settings as FormSettings;

/**
 * Class Mcommerce_Application_SettingsController
 */
class Mcommerce_Application_SettingsController extends Application_Controller_Default_Ajax
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
    ];

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

            $form = new FormSettings();
            if ($form->isValid($values)) {
                /** Do whatever you need when form is valid */
                $mCommerce = new Mcommerce();
                $mCommerce->find($values['mcommerce_id']);
                $mCommerce
                    ->addData($values)
                    ->save();

                $mCommerce->setAddTip($values['add_tip'] ? 'true' : 'false');
                $mCommerce->setGuestMode($values['guest_mode'] ? 'true' : 'false');

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
}