<?php

use Siberian\Json;

/**
 * Class Booking_Model_Booking
 */
class Booking_Model_Booking extends Core_Model_Default
{

    /**
     * Booking_Model_Booking constructor.
     * @param array $params
     */
    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->_db_table = 'Booking_Model_Db_Table_Booking';
        return $this;
    }

    /**
     * @param $value_id
     * @return array
     */
    public function getInappStates($value_id)
    {

        $in_app_states = [
            [
                "state" => "booking-view",
                "offline" => false,
                "params" => [
                    "value_id" => $value_id,
                ],
            ],
        ];

        return $in_app_states;
    }

    /**
     * GET Feature url for app init
     *
     * @param $optionValue
     * @return array
     */
    public function getAppInitUris ($optionValue)
    {
        $featureUrl = __url("/booking/mobile_view/index", [
            "value_id" => $optionValue->getId(),
        ]);
        $featurePath = __path("/booking/mobile_view/index", [
            "value_id" => $optionValue->getId(),
        ]);


        return [
            "featureUrl" => $featureUrl,
            "featurePath" => $featurePath,
        ];
    }

    /**
     * @param $option_value
     * @return array
     */
    public function getEmbedPayload($option_value = null)
    {
        $payload = [
            "stores" => [],
            "page_title" => $option_value->getTabbarName()
        ];

        try {
            $settings = Json::decode($option_value->getSettings());
        } catch (\Exception $e) {
            $settings = [
                "design" => "list",
                "date_format" => "MM/DD/YYYY HH:mm"
            ];
        }

        // Cover & description!
        $booking = $option_value->getObject();
        $settings["cover"] = empty($booking->getCover()) ?
            false : $booking->getCover();
        $settings["description"] = empty($booking->getDescription()) ?
            false : $booking->getDescription();
        $settings["datepicker"] = $booking->getDatepicker();

        $payload["settings"] = $settings;

        if ($this->getId()) {
            $store = new Booking_Model_Store();
            $stores = $store->findAll([
                "booking_id" => $this->getId()
            ]);

            foreach ($stores as $store) {
                $payload["stores"][] = [
                    "id" => $store->getId(),
                    "name" => $store->getStoreName()
                ];
            }
        }

        return $payload;
    }

    /**
     * @param $option_value
     * @param $design
     * @param $category
     */
    public function createDummyContents($option_value, $design, $category)
    {
        $dummy_content_xml = $this->_getDummyXml($design, $category);
        $this->setValueId($option_value->getId())->save();

        // Continue if dummy is empty!
        if (!$dummy_content_xml) {
            return;
        }

        if ($dummy_content_xml) {
            foreach ($dummy_content_xml->children() as $content) {
                $store = new Booking_Model_Store();

                foreach ($content->children() as $key => $value) {
                    $store->addData((string)$key, (string)$value);
                }

                $store->setBookingId($this->getId())
                    ->save();
            }
        }
    }

    /**
     * @param $option
     * @return $this
     */
    public function copyTo($option, $parent_id = null)
    {
        $store = new Booking_Model_Store();
        $stores = $store->findAll(['booking_id' => $this->getId()]);

        $this->setId(null)
            ->setValueId($option->getId())
            ->save();

        foreach ($stores as $store) {
            $store->setId(null)
                ->setBookingId($this->getId())
                ->save();
        }

        return $this;
    }

    /**
     * @param $option Application_Model_Option_Value
     * @return string
     * @throws Exception
     */
    public function exportAction($option, $export_type = null)
    {
        if ($option && $option->getId()) {

            $current_option = $option;
            $value_id = $current_option->getId();

            $booking_model = new Booking_Model_Booking();
            $booking = $booking_model->find($value_id, "value_id");

            $store_model = new Booking_Model_Store();

            $stores = $store_model->findAll([
                "booking_id = ?" => $booking->getId(),
            ]);

            $stores_data = [];
            foreach ($stores as $store) {
                $store_data = $store->getData();

                if ($export_type === "safe") {
                    $store_data["store_name"] = "Praesent sed neque.";
                    $store_data["email"] = "test@lorem-ipsum.test";
                }

                $stores_data[] = $store_data;
            }

            $dataset = [
                "option" => $current_option->forYaml(),
                "booking" => $booking->getData(),
                "stores" => $stores_data,
            ];

            try {
                $result = Siberian_Yaml::encode($dataset);
            } catch (Exception $e) {
                throw new Exception("#100-00: An error occured while exporting dataset to YAML.");
            }

            return $result;

        } else {
            throw new Exception("#100-02: Unable to export the feature, non-existing id.");
        }
    }

    /**
     * @param $path
     * @throws Exception
     */
    public function importAction($path)
    {
        $content = file_get_contents($path);

        try {
            $dataset = Siberian_Yaml::decode($content);
        } catch (Exception $e) {
            throw new Exception("#100-03: An error occured while importing YAML dataset '$path'.");
        }

        $application = $this->getApplication();
        $application_option = new Application_Model_Option_Value();

        if (isset($dataset["option"])) {
            $new_application_option = $application_option
                ->setData($dataset["option"])
                ->unsData("value_id")
                ->unsData("id")
                ->setData('app_id', $application->getId())
                ->save();

            $new_value_id = $new_application_option->getId();

            $new_booking = new Booking_Model_Booking();
            $new_booking
                ->setData($dataset["booking"])
                ->unsData("booking_id")
                ->unsData("id")
                ->save();

            /** Create Stores */
            if (isset($dataset["stores"]) && $new_value_id && $new_booking->getId()) {

                foreach ($dataset["stores"] as $store) {

                    $new_store = new Booking_Model_Store();
                    $new_store
                        ->setData($store)
                        ->unsData("store_id")
                        ->unsData("id")
                        ->setData("booking_id", $new_booking->getId())
                        ->save();
                }

            }

        } else {
            throw new Exception("#100-04: Missing option, unable to import data.");
        }
    }
}
