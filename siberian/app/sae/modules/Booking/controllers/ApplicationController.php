<?php

use Siberian\Feature;
use Siberian\Json;

/**
 * Class Booking_ApplicationController
 */
class Booking_ApplicationController extends Application_Controller_Default
{

    /**
     * @var array
     */
    public $cache_triggers = [
        "editpost" => [
            "tags" => [
                "homepage_app_#APP_ID#",
            ],
        ],
        "edit-settings" => [
            "tags" => [
                "homepage_app_#APP_ID#",
            ],
        ],
        "delete" => [
            "tags" => [
                "homepage_app_#APP_ID#",
            ],
        ]
    ];

    /**
     *
     */
    public function editSettingsAction()
    {
        try {
            $optionValue = $this->getCurrentOptionValue();
            $request = $this->getRequest();
            $values = $request->getPost();

            if (!$optionValue->getId()) {
                throw new Exception(p__("booking", "This feature doesn't exists!"));
            }

            if (empty($values)) {
                throw new Exception(p__("booking", "Values are required!"));
            }

            $form = new Booking_Form_Settings();
            if ($form->isValid($values)) {

                $booking = (new Booking_Model_Booking())->find($optionValue->getId(), "value_id");
                Feature::formImageForOption($optionValue, $booking, $values, "cover", true);
                $booking->setDatepicker($values["datepicker"]);
                $booking->setDescription($values["description"]);
                $booking->setValueId($optionValue->getId());
                $booking->save();

                $optionValue->setSettings(Json::encode([
                    "design" => $values["design"],
                    "date_format" => $values["date_format"]
                ]))->save();

                /** Update touch date, then never expires (until next touch) */
                $optionValue
                    ->touch()
                    ->expires(-1);

                // Clear cache on save!
                $this->cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, [
                    "booking",
                    "value_id_" . $optionValue->getId(),
                ]);

                $payload = [
                    "success" => true,
                    "message" => p__("booking", "Settings saved"),
                ];
            } else {
                $payload = [
                    "error" => true,
                    "message" => $form->getTextErrors(),
                    "errors" => $form->getTextErrors(true)
                ];
            }
        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     *
     */
    public function editpostAction()
    {

        try {

            if ($params = $this->getRequest()->getPost()) {

                $isNew = true;

                // Test s'il y a une erreur dans la saisie
                if (empty($params["store_name"])) {
                    throw new Siberian_Exception(__("Please, choose a store"));
                }

                if (empty($params['email'])) {
                    throw new Siberian_Exception(__("Please enter a valid email address"));
                }

                // Test s'il y a un value_id
                if (empty($params["value_id"])) {
                    throw new Siberian_Exception(__("An error occurred during process. Please try again later."));
                }

                // Récupère l'option_value en cours
                $option_value = new Application_Model_Option_Value();
                $option_value->find($params['value_id']);

                $booking = new Booking_Model_Booking();
                $store = new Booking_Model_Store();
                $booking->find($params['value_id'], 'value_id');
                // Si un id est passé en paramètre
                if (!empty($params["store_id"])) {
                    $store->find($params["store_id"]);
                    if ($store->getId() AND $booking->getValueId() != $option_value->getId()) {
                        // Envoi l'erreur
                        throw new Siberian_Exception(__("An error occurred during process. Please try again later."));
                    }
                    $isNew = !$store->getId();
                }

                $booking->setData($params)->save();
                unset($params["value_id"]);
                $params["booking_id"] = $booking->getId();
                $store->setData($params)->save();

                $data = [
                    "success" => true,
                    "success_message" => __("Info successfully saved"),
                    "message_timeout" => 2,
                    "message_button" => 0,
                    "message_loader" => 0
                ];

                if ($isNew) {
                    $data["row_html"] = $this->getLayout()->addPartial("row_" . $store->getId(), "admin_view_default", "booking/application/edit/row.phtml")
                        ->setCurrentStore($store)
                        ->setCurrentOptionValue($option_value)
                        ->toHtml();
                }

                /** Update touch date, then never expires (until next touch) */
                $option_value
                    ->touch()
                    ->expires(-1);

            } else {
                throw new Siberian_Exception(__("An error occurred during process. Please try again later."));
            }

        } catch (Exception $e) {
            $data = [
                "error" => true,
                "message" => $e->getMessage(),
                "message_button" => true,
                "message_loader" => true
            ];
        }

        $this->_sendJson($data);

    }

    /**
     *
     */
    public function deleteAction()
    {

        try {

            $id = $this->getRequest()->getParam("id");
            $store = new Booking_Model_Store();
            $store
                ->find($id)
                ->delete();

            # Success
            $data = [
                "success" => true
            ];

        } catch (Exception $e) {
            $data = [
                "error" => true,
                "message" => $e->getMessage(),
                "message_button" => 1,
                "message_loader" => 1
            ];
        }

        $this->_sendJson($data);
    }

    /**
     * @param $option
     * @return string
     * @throws Exception
     */
    public function exportAction()
    {
        if ($this->getCurrentOptionValue()) {
            $booking = new Booking_Model_Booking();
            $result = $booking->exportAction($this->getCurrentOptionValue());

            $this->_download($result, "booking-" . date("Y-m-d_h-i-s") . ".yml", "text/x-yaml");
        }
    }

}