<?php
class Booking_Model_Booking extends Core_Model_Default {

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Booking_Model_Db_Table_Booking';
        return $this;
    }

    /**
     * @return array
     */
    public function getInappStates($value_id) {

        $in_app_states = array(
            array(
                "state" => "booking-view",
                "offline" => false,
                "params" => array(
                    "value_id" => $value_id,
                ),
            ),
        );

        return $in_app_states;
    }

    /**
     * @param $option_value
     * @return bool
     */
    public function getEmbedPayload($option_value) {

        $payload = array(
            "stores" => array(),
            "page_title" => $option_value->getTabbarName()
        );

        if($this->getId()) {
            $store = new Booking_Model_Store();
            $stores = $store->findAll(array(
                "booking_id" => $this->getId()
            ));

            foreach($stores as $store) {
                $payload["stores"][] = array(
                    "id"    => $store->getId(),
                    "name"  => $store->getStoreName()
                );
            }
        }

        return $payload;

    }

    public function createDummyContents($option_value, $design, $category) {

        $dummy_content_xml = $this->_getDummyXml($design, $category);

        $this->setValueId($option_value->getId())->save();

        foreach ($dummy_content_xml->children() as $content) {
            $store = new Booking_Model_Store();

            foreach ($content->children() as $key => $value) {
                $store->addData((string)$key, (string)$value);
            }

            $store->setBookingId($this->getId())
                ->save()
            ;
        }
    }

    public function copyTo($option) {
        $store = new Booking_Model_Store();
        $stores = $store->findAll(array('booking_id' => $this->getId()));

        $this->setId(null)
            ->setValueId($option->getId())
            ->save()
        ;

        foreach($stores as $store) {
            $store->setId(null)
                ->setBookingId($this->getId())
                ->save()
            ;
        }

        return $this;
    }

    /**
     * @param $option Application_Model_Option_Value
     * @return string
     * @throws Exception
     */
    public function exportAction($option, $export_type = null) {
        if($option && $option->getId()) {

            $current_option = $option;
            $value_id = $current_option->getId();

            $booking_model = new Booking_Model_Booking();
            $booking = $booking_model->find($value_id, "value_id");

            $store_model = new Booking_Model_Store();

            $stores = $store_model->findAll(array(
                "booking_id = ?" => $booking->getId(),
            ));

            $stores_data = array();
            foreach($stores as $store) {
                $store_data = $store->getData();

                if($export_type === "safe") {
                    $store_data["store_name"] = "Praesent sed neque.";
                    $store_data["email"] = "test@lorem-ipsum.test";
                }

                $stores_data[] = $store_data;
            }

            $dataset = array(
                "option" => $current_option->forYaml(),
                "booking" => $booking->getData(),
                "stores" => $stores_data,
            );

            try {
                $result = Siberian_Yaml::encode($dataset);
            } catch(Exception $e) {
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
    public function importAction($path) {
        $content = file_get_contents($path);

        try {
            $dataset = Siberian_Yaml::decode($content);
        } catch(Exception $e) {
            throw new Exception("#100-03: An error occured while importing YAML dataset '$path'.");
        }

        $application = $this->getApplication();
        $application_option = new Application_Model_Option_Value();

        if(isset($dataset["option"])) {
            $new_application_option = $application_option
                ->setData($dataset["option"])
                ->unsData("value_id")
                ->unsData("id")
                ->setData('app_id', $application->getId())
                ->save()
            ;

            $new_value_id = $new_application_option->getId();

            $new_booking = new Booking_Model_Booking();
            $new_booking
                ->setData($dataset["booking"])
                ->unsData("booking_id")
                ->unsData("id")
                ->save()
            ;

            /** Create Stores */
            if(isset($dataset["stores"]) && $new_value_id && $new_booking->getId()) {

                foreach($dataset["stores"] as $store) {

                    $new_store = new Booking_Model_Store();
                    $new_store
                        ->setData($store)
                        ->unsData("store_id")
                        ->unsData("id")
                        ->setData("booking_id", $new_booking->getId())
                        ->save()
                    ;
                }

            }

        } else {
            throw new Exception("#100-04: Missing option, unable to import data.");
        }
    }
}
