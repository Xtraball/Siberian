<?php
class Booking_Model_Booking extends Core_Model_Default {

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Booking_Model_Db_Table_Booking';
        return $this;
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
}
