<?php

class Mcommerce_Model_Customer extends Customer_Model_Customer
{
    public static function getCleanMetas($mcommerce, $data)
    {
        $meta = array();
        $mcommerce->getPhone() != 'hidden' && $meta['phone'] = $data['metadatas']['phone'];
        $mcommerce->getBirthday() != 'hidden' && $meta['birthday'] = $data['metadatas']['birthday'];
        $mcommerce->getInvoicingAddress() != 'hidden' && $meta['invoicing_address'] = $data['metadatas']['invoicing_address'];
        $mcommerce->getDeliveryAddress() != 'hidden' && $meta['delivery_address'] = $data['metadatas']['delivery_address'];
        return $meta;
    }

    public static function getCleanInfos($mcommerce, $data)
    {
        $infos = array();
        $infos['firstname'] = $data['firstname'];
        $infos['lastname'] = $data['lastname'];
        $infos['email'] = $data['email'];
        $infos['metadatas'] = self::getCleanMetas($mcommerce, $data);
        return $infos;
    }

    public function populate($mcommerce, $data)
    {
        $this->setFirstname($data['firstname']);
        $this->setLastname($data['lastname']);
        $this->setEmail($data['email']);
        $this->setMetadatas(self::getCleanMetas($mcommerce, $data));
        return $this;
    }
}
