<?php

/**
 * Class Mcommerce_Model_Customer
 */
class Mcommerce_Model_Customer extends Customer_Model_Customer
{
    /**
     * @param $mcommerce
     * @param $data
     * @return array
     */
    public static function getCleanMetas($mcommerce, $data): array
    {
        $meta = [];
        $mcommerce->getPhone() !== 'hidden' && $meta['phone'] = $data['metadatas']['phone'];
        $mcommerce->getBirthday() !== 'hidden' && $meta['birthday'] = $data['metadatas']['birthday'];
        $mcommerce->getInvoicingAddress() !== 'hidden' && $meta['invoicing_address'] = $data['metadatas']['invoicing_address'];
        $mcommerce->getDeliveryAddress() !== 'hidden' && $meta['delivery_address'] = $data['metadatas']['delivery_address'];
        return $meta;
    }

    /**
     * @param $mcommerce
     * @param $data
     * @return array
     */
    public static function getCleanInfos($mcommerce, $data): array
    {
        $infos = [];
        $infos['firstname'] = $data['firstname'];
        $infos['lastname'] = $data['lastname'];
        $infos['email'] = $data['email'];
        $infos['metadatas'] = self::getCleanMetas($mcommerce, $data);
        return $infos;
    }

    /**
     * @param $mcommerce
     * @param $data
     * @return $this
     * @throws \rock\sanitize\SanitizeException
     */
    public function populate($mcommerce, $data): self
    {
        $this->setFirstname($data['firstname']);
        $this->setLastname($data['lastname']);
        $this->setEmail($data['email']);
        $this->setMetadatas(self::getCleanMetas($mcommerce, $data));
        return $this;
    }
}
