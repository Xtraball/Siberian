<?php

class Mcommerce_Application_Settings_DiscountController extends Application_Controller_Default_Ajax {

    public function editAction() {
        $promo = new Mcommerce_Model_Promo();
        $mcommerce = $this->getCurrentOptionValue()->getObject();
        if ($id = $this->getRequest()->getParam('promo_id')) {
            $promo->find($id);
            if ($promo->getId() AND $mcommerce->getId() != $promo->getMcommerceId()) {
                throw new Exception($this->_('An error occurred during the process. Please try again later.'));
            }
        }

        $html = $this->getLayout()->addPartial('promo_form', 'admin_view_default', 'mcommerce/application/edit/settings/discount/edit.phtml')
            ->setOptionValue($this->getCurrentOptionValue())
            ->setCurrentPromo($promo)
            ->toHtml();

        $html = array('form_html' => $html);
        $this->_sendHtml($html);
    }

    public function editpostAction() {
        if ($datas = $this->getRequest()->getPost()) {
            $fields = array(
                'label' => "Discount label is missing",
                'code' => "Discount code is missing",
                'type' => "Discount type is missing",
                'discount' => "Discounted amount is missing",
            );
            $errors = array();

            try {
                foreach ($fields as $field_name => $error_message) {
                    if (!$datas[$field_name]) {
                        $errors[] = $this->_($error_message);
                    }
                }

                if ($datas['type'] == "percentage") {
                    if ($datas['discount'] > 99 || $datas['discount'] < 1) {
                        $errors[] = $this->_("Percentage must be at least 1 percent, at most 99 percent");
                    }
                }
                $float_validator = new Zend_Validate_Float();
                if ($datas['minimum_amount'] && (!is_numeric($datas['minimum_amount']) || $datas['minimum_amount'] < 0)) {
                    $errors[] = $this->_("Minimum amount should be a positive number");
                }
                if (!is_numeric($datas['discount']) || $datas['discount'] < 0) {
                    $errors[] = $this->_("Discounted amount should be a positive number");
                }

                $promo = new Mcommerce_Model_Promo();
                $mcommerce = $this->getCurrentOptionValue()->getObject();
                $isNew = true;

                if ($datas['promo_id']) {
                    $promo->find(array('promo_id' => $datas['promo_id'], 'mcommerce_id' => $mcommerce->getMcommerceId()));
                    if (!$promo->getId()) {
                        $errors[] = $this->_("Promo not found");
                    } else {
                        $isNew = false;
                    }
                }

                if (sizeof($errors) > 0) {
                    $message = $this->_('Please correct the following errors <br>') . implode('<br>', $errors);
                    throw new Exception($message);
                }

                $valid_until = $datas['valid_until'] ? new Zend_Date($datas['valid_until']) : null;
                if($valid_until) {
                    $valid_until = $valid_until->get(Zend_Date::ISO_8601);
                }

                $promo
                    ->setMcommerceId($mcommerce->getMcommerceId())
                    ->setEnabled($datas['enabled'] === 'on')
                    ->setUseOnce($datas['use_once'] === 'on')
                    ->setLabel($datas['label'])
                    ->setCode($datas['code'])
                    ->setType($datas['type'])
                    ->setDiscount($datas['discount'])
                    ->setValidUntil($valid_until)
                    ->setMinimumAmount($datas['minimum_amount'])
                    ->verifyUnique()
                    ->save();


                $html = array(
                    'promo_id' => $promo->getPromoId(),
                    'success' => '1',
                    'success_message' => $this->_('Discount successfully saved'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                );

                if ($isNew) {
                    $html['row_html'] = $this->getLayout()->addPartial('row_promo_' . $promo->getId(), 'admin_view_default', 'mcommerce/application/edit/settings/discount/li.phtml')
                        ->setCurrentOptionValue($this->getCurrentOptionValue())
                        ->setCurrentPromo($promo)
                        ->toHtml();

                } else {
                    $html['promo_name'] = $promo->getLabel();
                }

            } catch (Exception $e) {
                $html = array(
                    'error' => 1,
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            }
            $this->_sendHtml($html);
        }

    }

    public function removeAction() {

        $promo = new Mcommerce_Model_Promo();

        try {
            if ($id = $this->getRequest()->getParam('promo_id')) {

                $mcommerce = $this->getCurrentOptionValue()->getObject();
                $promo->find(array('promo_id' => $id, 'mcommerce_id' => $mcommerce->getMcommerceId()));

                if (!$promo->getId()) {
                    throw new Exception($this->_('An error occurred during the process. Please try again later.'));
                }
                $promo_id = $promo->getId();

                $promo->setIsDeleted(1)->save();

                $html = array(
                    'promo_id' => $promo_id,
                    'success' => '1',
                    'success_message' => $this->_('Promo successfully deleted'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                );

            } else {
                throw new Exception($this->_('An error occurred during the process. Please try again later.'));
            }
        } catch (Exception $e) {
            $html = array(
                'error' => 1,
                'message' => $e->getMessage(),
                'message_button' => 1,
                'message_loader' => 1
            );
        }

        $this->_sendHtml($html);

    }
}
