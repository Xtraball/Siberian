<?php

class Mcommerce_Application_Settings_TaxController extends Application_Controller_Default_Ajax {

    public function editAction() {

        $tax = new Mcommerce_Model_Tax();
        $mcommerce = $this->getCurrentOptionValue()->getObject();
        if($id = $this->getRequest()->getParam('tax_id')) {
            $tax->find($id);
            if($tax->getId() AND $mcommerce->getId() != $tax->getMcommerceId()) {
                throw new Exception($this->_('An error occurred during the process. Please try again later.'));
            }
        }

        $html = $this->getLayout()->addPartial('tax_form', 'admin_view_default', 'mcommerce/application/edit/settings/tax/edit.phtml')
            ->setOptionValue($this->getCurrentOptionValue())
            ->setCurrentTax($tax)
            ->toHtml();

        $html = array('form_html' => $html);

        $this->_sendHtml($html);
    }

    public function editpostAction() {


        if($datas = $this->getRequest()->getPost()) {

            try {

                $rate = isset($datas['rate']) ? $datas['rate'] : null;

                if(is_null($rate)) {
                    throw new Exception($this->_('An error occurred while saving. Please try again later.'));
                }

                $datas['rate'] = $rate;
                $isNew = false;
                $mcommerce = $this->getCurrentOptionValue()->getObject();
                $tax = new Mcommerce_Model_Tax();
                if(!empty($datas['tax_id'])) {
                    $tax->find($datas['tax_id']);
                    if($tax->getId() AND $mcommerce->getId() != $tax->getMcommerceId()) {
                        throw new Exception($this->_('An error occurred while saving. Please try again later.'));
                    }
                }

                if(!$tax->getId()) {
                    $datas['mcommerce_id'] = $mcommerce->getId();
                    $isNew = true;
                }

                $tax->setData($datas)->save();

                $html = array(
                    'tax_id' => $tax->getId(),
                    'success' => '1',
                    'success_message' => $this->_('Tax successfully saved'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                );

                if($isNew) {
                    $html['row_html'] = $this->getLayout()->addPartial('row_tax_'.$tax->getId(), 'admin_view_default', 'mcommerce/application/edit/settings/tax/li.phtml')
                        ->setCurrentOptionValue($this->getCurrentOptionValue())
                        ->setCurrentTax($tax)
                        ->toHtml()
                    ;

                }
                else {
                    $html['tax_name'] = $tax->getName();
                }

            }
            catch(Exception $e) {
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

        $tax = new Mcommerce_Model_Tax();

        try {
            if($id = $this->getRequest()->getParam('tax_id')) {

                $mcommerce = $this->getCurrentOptionValue()->getObject();
                $tax->find($id);
                if(!$tax->getId() OR $mcommerce->getId() != $tax->getMcommerceId()) {
                    throw new Exception($this->_('An error occurred during the process. Please try again later.'));
                }

                $tax->setIsDeleted(1)->save();

                $html = array(
                    'tax_id' => $tax->getId(),
                    'success' => '1',
                    'success_message' => $this->_('Tax successfully deleted'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                );

            }
            else {
                throw new Exception($this->_('An error occurred during the process. Please try again later.'));
            }
        }
        catch(Exception $e) {
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