<?php

class Promotion_MobileController extends Application_Controller_Mobile_Default {

    public function validateAction() {

        try {
            $customer_id = $this->getSession()->getCustomerId();
            if(!$customer_id) throw new Exception($this->_('You must be logged in to use a discount'));
            $html = array();

            if($promotion_id = $this->getRequest()->getPost('promotion_id')) {

                // Prépare la promotion
                $promotion = new Promotion_Model_Promotion();
                $promotion->find($promotion_id);

                // Prépare la promotion du client
                $promotion_customer = new Promotion_Model_Customer();
                $promotion_customer->findLast($promotion_id, $customer_id);

                if(!$promotion_customer->getId()) {
                    $promotion_customer->setPromotionId($promotion_id)
                        ->setCustomerId($customer_id)
                    ;
                }

                if($promotion->getIsUnique() AND $promotion_customer->getId() AND $promotion_customer->getIsUsed()) {
                    $html['close'] = true;
                    throw new Exception($this->_('You have already use this discount'));
                }
                else {
                    $promotion_customer->setIsUsed(1)->save();
                    $html = array('ok' => true);
                }

            }
        }
        catch(Exception $e) {
            $html['error'] = 1;
            $html['message'] = $e->getMessage();
        }

        $this->_sendHtml($html);
    }

}