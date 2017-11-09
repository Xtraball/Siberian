<?php

class Promotion_Mobile_ListController extends Application_Controller_Mobile_Default {

    public function findallAction() {

        if ($value_id = $this->getRequest()->getParam('value_id')) {

            $option = $this->getCurrentOptionValue();
            $promotion_customer = new Promotion_Model_Customer();
            $promotion_customers = $promotion_customer
                ->findAllByValue($option->getId(), $this->getSession()->getCustomerId() | 0);

            $payload = ['promotions' => []];

            if ($promotion_customers->count() == 0) {
                $data['promotions'] = [];
            }

            $tc = new Application_Model_Tc();
            $tc->findByType($this->getApplication()->getId(), 'discount');
            $text = $tc->getText();
            $payload['tc_id'] = !empty($text) ? $tc->getId() : null;

            foreach ($promotion_customers as $promotion_customer) {

                $pictureUrl = $this->getRequest()->getBaseUrl() .
                    '/app/sae/modules/Promotion/resources/images/coupon-default.jpg';
                if ($promotion_customer->getPictureUrl()) {
                    $pictureUrl = $this->getRequest()->getBaseUrl() .
                        $promotion_customer->getPictureUrl();
                }

                $thumbnailUrl = $this->getRequest()->getBaseUrl() .
                    '/app/sae/modules/Promotion/resources/images/coupon-default-thumbnail.jpg';
                if ($promotion_customer->getThumbnailUrl()) {
                    $thumbnailUrl = $this->getRequest()->getBaseUrl() .
                        $promotion_customer->getThumbnailUrl();
                }

                $title = $promotion_customer->getTitle();
                $subtitle = $promotion_customer->getDescription();
                $url = $this->getPath('promotion/mobile_view', [
                    'value_id' => $value_id,
                    'promotion_id' => $promotion_customer->getPromotionId()
                ]);
                $is_locked = false;

                // Write QRCode file in place!
                $image_name = $promotion_customer->getId() . '-qrpromotion_qrcode.png';
                $file = Core_Model_Directory::getBasePathTo('/images/application/' .
                    $this->getApplication()->getId() . '/application/qrpromotion/' .
                    $image_name);

                if ($promotion_customer->getUnlockCode() &&
                    !$promotion_customer->getCustomerId()) {
                    $pictureUrl = $this->getRequest()->getBaseUrl() .
                        '/images/library/code_scan/qrcode.png';
                    $title = __('Scan it.');
                    $subtitle = '';
                    $url = '';
                    $is_locked = true;
                }

                $tc_id = null;
                if ($payload['tc_id']) {
                    $tc_id = (integer) $data['tc_id'];
                }

                $embed_payload = [
                    'id' => (integer) $promotion_customer->getPromotionId(),
                    'picture' => $pictureUrl,
                    'thumbnail' => $thumbnailUrl,
                    'title' => $promotion_customer->getTitle(),
                    'description' => html_entity_decode(strip_tags($promotion_customer->getDescription())),
                    'description_html' => $promotion_customer->getDescription(),
                    'conditions' => $promotion_customer->getConditions(),
                    'is_unique' => (boolean) $promotion_customer->getIsUnique(),
                    'end_at' => datetime_to_format($promotion_customer->getEndAt(), Zend_Date::DATE_SHORT),
                    'confirm_message' => __('Do you want to use this coupon?'),
                    'page_title' => $promotion_customer->getTitle(),
                    'tc_id' => $tc_id
                ];

                $payload['promotions'][] = [
                    'id' => (integer) $promotion_customer->getPromotionId(),
                    'picture' => $pictureUrl,
                    'thumbnail' => $thumbnailUrl,
                    'title' => $title,
                    'subtitle' => html_entity_decode(strip_tags($subtitle)),
                    'subtitle_html' => $subtitle,
                    'url' => $url,
                    'is_locked' => (boolean) $is_locked,
                    'embed_payload' => $embed_payload
                ];

            }

            $payload['social_sharing_is_active'] = $option->getSocialSharingIsActive();
            $payload['page_title'] = $option->getTabbarName();
            $payload['modal_title'] = __('Do you want to use this coupon?');

        } else {
            $payload = [
                'error' => true,
                'message' => '#08-564: ' . __('An unknown error occured.')
            ];
        }

        $this->_sendJson($payload);
    }


    public function useAction() {

        try {
            $customer_id = $this->getSession()->getCustomerId();

            if (!$customer_id) {
                throw new Siberian_Exception(__('You must be logged in to use a discount'));
            }

            if ($data = Siberian_Json::decode($this->getRequest()->getRawBody())) {

                if (empty($data['promotion_id'])) {
                    throw new Siberian_Exception(__('An error occurred while saving. Please try again later.'));
                }

                $promotion_id = $data['promotion_id'];

                $promotion = new Promotion_Model_Promotion();
                $promotion->find($promotion_id);

                $promotion_customer = new Promotion_Model_Customer();
                $promotion_customer->findLast($promotion_id, $customer_id);

                if (!$promotion_customer->getId()) {
                    $promotion_customer->setPromotionId($promotion_id)
                        ->setCustomerId($customer_id)
                    ;
                }

                if ($promotion->getIsUnique() &&
                    $promotion_customer->getId() &&
                    $promotion_customer->getIsUsed()) {
                    $payload = [
                        'success' => true,
                        'message' => __('You have already use this discount'),
                        'remove' => true
                    ];
                } else {
                    $promotion_customer
                        ->setIsUsed(1)
                        ->save();

                    $payload = [
                        'success' => true,
                        'message' => __('This discount is now used'),
                        'remove' => true
                    ];
                }
            } else {
                throw new Siberian_Exception(__('Missing data.'));
            }

        } catch (Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }

    public function unlockbyqrcodeAction() {

        try {

            $customer_id = $this->getSession()->getCustomerId();
            if (!$customer_id) {
                throw new Siberian_Exception(__('You must be logged in to use a discount'));
            }

            if ($data = Zend_Json::decode($this->getRequest()->getRawBody())) {

                $promotion = new Promotion_Model_Promotion();
                $promotion->find([
                    'unlock_code' => $data['qrcode'],
                    'value_id' => $data['value_id']
                ]);

                $promotion_id = $promotion->getId();

                $promotion_customer = new Promotion_Model_Customer();
                $promotion_customer->findLast($promotion_id, $customer_id);

                if ($promotion->getUnlockCode() != $data['qrcode']) {
                    throw new Siberian_Exception(__('This code is unrecognized'));
                }

                if ($promotion_customer->getIsUsed() != '' && $promotion_customer->getIsUsed() == 0) {
                    throw new Siberian_Exception(__('You have already use this code'));
                }

                if (!$promotion_customer->getId()) {
                    $promotion_customer->setPromotionId($promotion_id)
                        ->setCustomerId($customer_id)
                    ;
                }

                $promotion_customer->setIsUsed(0)->save();

                $promotion_data = [
                    'id' => $promotion->getPromotionId(),
                    'picture' => $this->getRequest()->getBaseUrl() . $promotion->getPictureUrl(),
                    'title' => $promotion->getTitle(),
                    'subtitle' => $promotion->getDescription(),
                    'url' => $this->getPath('promotion/mobile_view', [
                        'value_id' => $data['value_id'],
                        'promotion_id' => $promotion->getPromotionId()
                    ]),
                    'is_locked' => false
                ];

                $payload = [
                    'success' => true,
                    'promotion' => $promotion_data
                ];

            } else {
                throw new Siberian_Exception(__('An error occured.'));
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
