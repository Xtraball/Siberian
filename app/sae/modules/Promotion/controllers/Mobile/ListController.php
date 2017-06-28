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

            foreach($promotion_customers as $promotion_customer) {

                $picture = $promotion_customer->getPictureUrl() ? $this->getRequest()->getBaseUrl().$promotion_customer->getPictureUrl() : null;
                $title = $promotion_customer->getTitle();
                $subtitle = $promotion_customer->getDescription();
                $url = $this->getPath("promotion/mobile_view", array("value_id" => $value_id, "promotion_id" => $promotion_customer->getPromotionId()));
                $is_locked = false;

                if($promotion_customer->getUnlockCode() && !$promotion_customer->getCustomerId()) {
                    $picture = $this->getRequest()->getBaseUrl() . "/images/library/code_scan/qrcode.png";
                    $title = $this->_("Scan it.");
                    $subtitle = "";
                    $url = "";
                    $is_locked = true;
                }

                $data['promotions'][] = array(
                    "id" => $promotion_customer->getPromotionId(),
                    "picture" => $picture,
                    "title" => $title,
                    "subtitle" => $subtitle,
                    "url" => $url,
                    "is_locked" => $is_locked
                );

                /*
                switch($option->getLayoutId()) {
                    case 2:
                    case 3:
                    case 4:
                        $data['promotions'][] = array(
                            "id" => $promotion_customer->getPromotionId(),
                            "picture" => $this->getRequest()->getBaseUrl().$promotion_customer->getPictureUrl(),
                            "title" => $promotion_customer->getTitle(),
                            "subtitle" => $promotion_customer->getDescription(),
                            "url" => $this->getPath("promotion/mobile_view", array("value_id" => $value_id, "promotion_id" => $promotion_customer->getPromotionId())),
                        );
                    break;
                    case 1:
                    default:
                        $data['promotions'][] = array(
                            "id" => $promotion_customer->getPromotionId(),
                            "picture" => $this->getRequest()->getBaseUrl().$promotion_customer->getPictureUrl(),
                            "title" => $promotion_customer->getTitle(),
                            "description" => $promotion_customer->getDescription(),
                            "conditions" => $promotion_customer->getConditions(),
                            "is_unique" => $promotion_customer->getIsUnique(),
                            "end_at" => $promotion_customer->getFormattedEndAt($this->_('MMMM dd y')),
                            "unlock_code" => $promotion_customer->getUnlockCode(),
                            "is_used" => $promotion_customer->getIsUsed()
                        );
                    break;
                }
                */
            }

            $data["social_sharing_is_active"] = $option->getSocialSharingIsActive();
            $data['page_title'] = $option->getTabbarName();

            $tc = new Application_Model_Tc();
            $tc->findByType($this->getApplication()->getId(), "discount");
            $text = $tc->getText();
            $data["tc_id"] = !empty($text) ? $tc->getId() : null;

            $this->_sendHtml($data);

        }
    }


    public function useAction() {

        try {
            $customer_id = $this->getSession()->getCustomerId();
            if(!$customer_id) throw new Exception($this->_('You must be logged in to use a discount'));
            $html = array();

            if($data = Zend_Json::decode($this->getRequest()->getRawBody())) {

                if(empty($data['promotion_id'])) {
                    throw new Exception($this->_("An error occurred while saving. Please try again later."));
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
                    $html['remove'] = true;
                    throw new Exception($this->_('You have already use this discount'));
                }
                else {
                    $promotion_customer->setIsUsed(1)->save();
                    $html = array(
                        "success" => 1,
                        "message" => $this->_("This discount is now used"),
                        "remove" => 1
                    );
                }

            }
        }
        catch(Exception $e) {
            $html['error'] = 1;
            $html['message'] = $e->getMessage();
        }

        $this->_sendHtml($html);
    }

    public function unlockbyqrcodeAction() {

        try {

            $customer_id = $this->getSession()->getCustomerId();
            if(!$customer_id) throw new Exception($this->_('You must be logged in to use a discount'));

            if ($data = Zend_Json::decode($this->getRequest()->getRawBody())) {

                $promotion = new Promotion_Model_Promotion();
                $promotion->find(array("unlock_code" => $data["qrcode"], "value_id" => $data["value_id"]));

                $promotion_id = $promotion->getId();

                $promotion_customer = new Promotion_Model_Customer();
                $promotion_customer->findLast($promotion_id, $customer_id);

                if($promotion->getUnlockCode() != $data["qrcode"]) {
                    throw new Exception($this->_("This code is unrecognized"));
                }
                if($promotion_customer->getIsUsed() != "" && $promotion_customer->getIsUsed() == 0) {
                    throw new Exception($this->_("You have already use this code"));
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
