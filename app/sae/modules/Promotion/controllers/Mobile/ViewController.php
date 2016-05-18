<?php

class Promotion_Mobile_ViewController extends Application_Controller_Mobile_Default {

    public function findAction() {

        if($value_id = $this->getRequest()->getParam('value_id') AND $promotion_id = $this->getRequest()->getParam('promotion_id')) {

            $option = $this->getCurrentOptionValue();
            $promotion = new Promotion_Model_Promotion();
            $promotion->find($promotion_id);

            $data["promotion"] = array(
                "id" => $promotion->getPromotionId(),
                "picture" => $promotion->getPictureUrl() ? $this->getRequest()->getBaseUrl().$promotion->getPictureUrl() : null,
                "title" => $promotion->getTitle(),
                "description" => $promotion->getDescription(),
                "conditions" => $promotion->getConditions(),
                "is_unique" => (bool) $promotion->getIsUnique(),
                "end_at" => $promotion->getFormattedEndAt($this->_('MMMM dd y')),
            );

            $data["confirm_message"] = $this->_("Do you want to use this coupon?");
            $data["social_sharing_is_active"] = $option->getSocialSharingIsActive();
            $data["page_title"] = $promotion->getTitle();

            $tc = new Application_Model_Tc();
            $tc->findByType($this->getApplication()->getId(), "discount");
            $text = $tc->getText();
            $data["tc_id"] = !empty($text) ? $tc->getId() : null;

            $this->_sendHtml($data);

        }
    }

}