<?php

use Siberian\Exception;
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
    public function loadFormAction()
    {
        try {
            $request = $this->getRequest();
            $storeId = $request->getParam("store_id", null);
            $optionValue = $this->getCurrentOptionValue();

            $store = (new Booking_Model_Store())->find($storeId);

            if (!$store->getId()) {
                throw new Exception(p__("booking", "This feature doesn't exists!"));
            }

            $form = new Booking_Form_Location();
            $form->populate($store->getData());
            $form->setValueId($optionValue->getId());
            $form->removeNav("booking-location-nav");
            $form->addNav("booking-location-edit-nav", "Save", false);
            $form->setStoreId($store->getId());

            $payload = [
                "success" => true,
                "form" => $form->render(),
                "message" => __("Success."),
            ];
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
    public function editPostAction()
    {
        try {
            $request = $this->getRequest();
            $values = $request->getPost();
            $optionValue = $this->getCurrentOptionValue();

            if (empty($values)) {
                throw new Exception(p__("booking", "Missing params."));
            }

            $form = new Booking_Form_Location();
            if ($form->isValid($values)) {
                /** Do whatever you need when form is valid */
                $store = new Booking_Model_Store();
                $store->find($values["store_id"]);
                $store
                    ->addData($values)
                    ->save();

                // Update touch date, then never expires!
                $optionValue
                    ->touch()
                    ->expires(-1);

                $payload = [
                    "success" => true,
                    "message" => __("Success."),
                ];
            } else {
                /** Do whatever you need when form is not valid */
                $payload = [
                    "error" => true,
                    "message" => $form->getTextErrors(),
                    "errors" => $form->getTextErrors(true),
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
     * Delete category
     */
    public function deletePostAction()
    {
        $values = $this->getRequest()->getPost();

        $form = new Booking_Form_Location_Delete();
        if ($form->isValid($values)) {
            $store = new Booking_Model_Store();
            $store->find($values["store_id"]);
            $store->delete();

            // Update touch date, then never expires!
            $this->getCurrentOptionValue()
                ->touch()
                ->expires(-1);

            $payload = [
                "success" => true,
                "message" => p__("booking", "Store deleted."),
            ];
        } else {
            $payload = [
                "error" => true,
                "message" => $form->getTextErrors(),
                "errors" => $form->getTextErrors(true),
            ];
        }

        $this->_sendJson($payload);
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