<?php

class Promotion_Mobile_ViewController extends Application_Controller_Mobile_Default {

    /**
     * @deprecated in Siberian 5.0
     */
    public function findAction() {

        try {
            if($value_id = $this->getRequest()->getParam('value_id') AND $promotion_id = $this->getRequest()->getParam('promotion_id')) {

                $option = $this->getCurrentOptionValue();
                $promotion = new Promotion_Model_Promotion();
                $promotion->find($promotion_id);

                $end_at = $promotion->getEndAt();

                $payload['promotion'] = array(
                    'id' => $promotion->getPromotionId(),
                    'picture' => $promotion->getPictureUrl() ?
                        $this->getRequest()->getBaseUrl().$promotion->getPictureUrl() : null,
                    'title' => $promotion->getTitle(),
                    'description' => html_entity_decode(strip_tags($promotion->getDescription())),
                    'description_html' => $promotion->getDescription(),
                    'conditions' => $promotion->getConditions(),
                    'is_unique' => (bool) $promotion->getIsUnique(),
                    'end_at' => (!empty($end_at)) ?
                        datetime_to_format($promotion->getEndAt(), Zend_Date::DATE_SHORT) : null,
                );

                $payload['confirm_message'] = __('Do you want to use this coupon?');
                $payload['social_sharing_is_active'] = $option->getSocialSharingIsActive();
                $payload['page_title'] = $promotion->getTitle();

                $payload['promotion']['confirm_message'] = __('Do you want to use this coupon?');
                $payload['promotion']['social_sharing_is_active'] = $option->getSocialSharingIsActive();
                $payload['promotion']['page_title'] = $promotion->getTitle();

                $tc = new Application_Model_Tc();
                $tc->findByType($this->getApplication()->getId(), 'discount');
                $text = $tc->getText();
                $payload['tc_id'] = !empty($text) ? $tc->getId() : null;
                $payload['promotion']['tc_id'] = !empty($text) ? $tc->getId() : null;
            }

        } catch (Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }

}