<?php

class Promotion_Mobile_ListController extends Application_Controller_Mobile_Default {

    public function findallAction() {

        if($value_id = $this->getRequest()->getParam('value_id')) {

            $option = $this->getCurrentOptionValue();
            $promotion_customer = new Promotion_Model_Customer();
            $promotion_customers = $promotion_customer->findAllByValue($option->getId(), $this->getSession()->getCustomerId() | 0);

            $data = array("promotions" => array());

            if($promotion_customers->count() == 0) {
                $data['promotions'] = array();
            }

            $tc = new Application_Model_Tc();
            $tc->findByType($this->getApplication()->getId(), "discount");
            $text = $tc->getText();
            $data["tc_id"] = !empty($text) ? $tc->getId() : null;

            foreach($promotion_customers as $promotion_customer) {

                $picture = $promotion_customer->getPictureUrl() ? $this->getRequest()->getBaseUrl().$promotion_customer->getPictureUrl() : null;
                $title = $promotion_customer->getTitle();
                $subtitle = $promotion_customer->getDescription();
                $url = $this->getPath("promotion/mobile_view", array("value_id" => $value_id, "promotion_id" => $promotion_customer->getPromotionId()));
                $is_locked = false;

                if($promotion_customer->getUnlockCode() && !$promotion_customer->getCustomerId()) {
                    $picture = $this->getRequest()->getBaseUrl() . "/images/library/code_scan/qrcode.png";
                    $title = __("Scan it.");
                    $subtitle = "";
                    $url = "";
                    $is_locked = true;
                }

                $tc_id = null;
                if($data["tc_id"]) {
                    $tc_id = (integer) $data["tc_id"];
                }

                $embed_payload = array(
                    "id"            => (integer) $promotion_customer->getPromotionId(),
                    "picture"       => $promotion_customer->getPictureUrl() ? $this->getRequest()->getBaseUrl().$promotion_customer->getPictureUrl() : null,
                    "title"         => $promotion_customer->getTitle(),
                    "description"   => $promotion_customer->getDescription(),
                    "conditions"    => $promotion_customer->getConditions(),
                    "is_unique"     => (boolean) $promotion_customer->getIsUnique(),
                    "end_at"        => datetime_to_format($promotion_customer->getEndAt()),
                    "confirm_message" => __("Do you want to use this coupon?"),
                    "page_title"    => $promotion_customer->getTitle(),
                    "tc_id"         => $tc_id
                );

                $data["promotions"][] = array(
                    "id"                => (integer) $promotion_customer->getPromotionId(),
                    "picture"           => $picture,
                    "title"             => $title,
                    "subtitle"          => $subtitle,
                    "url"               => $url,
                    "is_locked"         => (boolean) $is_locked,
                    "embed_payload"     => $embed_payload
                );

            }

            $data["social_sharing_is_active"] = $option->getSocialSharingIsActive();
            $data['page_title'] = $option->getTabbarName();

            $this->_sendJson($data);

        }
    }


    public function useAction() {

        try {
            $customer_id = $this->getSession()->getCustomerId();

            if(!$customer_id) {
                throw new Siberian_Exception(__('You must be logged in to use a discount'));
            }

            if($data = Siberian_Json::decode($this->getRequest()->getRawBody())) {

                if(empty($data['promotion_id'])) {
                    throw new Siberian_Exception(__("An error occurred while saving. Please try again later."));
                }

                $promotion_id = $data['promotion_id'];

                $promotion = new Promotion_Model_Promotion();
                $promotion->find($promotion_id);

                $promotion_customer = new Promotion_Model_Customer();
                $promotion_customer->findLast($promotion_id, $customer_id);

                if(!$promotion_customer->getId()) {
                    $promotion_customer->setPromotionId($promotion_id)
                        ->setCustomerId($customer_id)
                    ;
                }

                if($promotion->getIsUnique() AND $promotion_customer->getId() AND $promotion_customer->getIsUsed()) {

                    $data = array(
                        "success"   => true,
                        "message"   => __('You have already use this discount'),
                        "remove"    => true
                    );

                }
                else {
                    $promotion_customer->setIsUsed(1)->save();

                    $data = array(
                        "success"   => true,
                        "message"   => __("This discount is now used"),
                        "remove"    => true
                    );
                }

            } else {
                throw new Siberian_Exception(__("Missing data."));
            }


        } catch(Exception $e) {
            $data = array(
                "error"     => true,
                "message"   => $e->getMessage()
            );
        }

        $this->_sendJson($data);
    }

    public function unlockbyqrcodeAction() {

        try {

            $customer_id = $this->getSession()->getCustomerId();
            if(!$customer_id) throw new Exception(__('You must be logged in to use a discount'));

            if ($data = Zend_Json::decode($this->getRequest()->getRawBody())) {

                $promotion = new Promotion_Model_Promotion();
                $promotion->find(array("unlock_code" => $data["qrcode"], "value_id" => $data["value_id"]));

                $promotion_id = $promotion->getId();

                $promotion_customer = new Promotion_Model_Customer();
                $promotion_customer->findLast($promotion_id, $customer_id);

                if($promotion->getUnlockCode() != $data["qrcode"]) {
                    throw new Exception(__("This code is unrecognized"));
                }
                if($promotion_customer->getIsUsed() != "" && $promotion_customer->getIsUsed() == 0) {
                    throw new Exception(__("You have already use this code"));
                }

                if(!$promotion_customer->getId()) {
                    $promotion_customer->setPromotionId($promotion_id)
                        ->setCustomerId($customer_id)
                    ;
                }
                $promotion_customer->setIsUsed(0)->save();

                $promotion_data = array(
                    "id" => $promotion->getPromotionId(),
                    "picture" => $this->getRequest()->getBaseUrl().$promotion->getPictureUrl(),
                    "title" => $promotion->getTitle(),
                    "subtitle" => $promotion->getDescription(),
                    "url" => $this->getPath("promotion/mobile_view", array("value_id" => $data["value_id"], "promotion_id" => $promotion->getPromotionId())),
                    "is_locked" => false
                );

                $html = array(
                    "success" => 1,
                    "promotion" => $promotion_data
                );

            }
        } catch(Exception $e) {
            $html = array(
                "error" => 1,
                "message" => $e->getMessage()
            );
        }

        $this->_sendHtml($html);
    }

}
