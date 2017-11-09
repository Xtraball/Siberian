<?php

class Mcommerce_Application_Settings_FieldsController extends Application_Controller_Default_Ajax
{

    public function saveAction()
    {

        if ($datas = $this->getRequest()->getPost()) {

            try {

                $mcommerce = new Mcommerce_Model_Mcommerce();
                $mcommerce->find(array("value_id" => $datas["option_value_id"]));
                if ($mcommerce->getId()) {
                    $mcommerce->setPhone($datas["phone"]);
                    $mcommerce->setBirthday($datas["birthday"]);
                    $mcommerce->setInvoicingAddress($datas["invoicing_address"]);
                    $mcommerce->setDeliveryAddress($datas["delivery_address"]);
                    $mcommerce->save();
                }

                $html = array(
                    'success' => '1',
                    'success_message' => $this->_('Settings successfully saved'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                );

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

}
