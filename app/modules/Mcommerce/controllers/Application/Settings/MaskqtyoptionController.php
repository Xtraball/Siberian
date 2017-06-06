<?php
  
class Mcommerce_Application_Settings_MaskqtyoptionController extends Application_Controller_Default_Ajax { 

    public function saveAction() {  

        if($datas = $this->getRequest()->getPost()) {

            try {

                $mcommerce = new Mcommerce_Model_Mcommerce();
                $mcommerce->find(array("value_id" => $datas["option_value_id"]));
                $mask_qty_opt = $datas["mask_qty_opt"]?1:0;
                if($mcommerce->getId()) {
                    $mcommerce->setMaskQtyOpt($mask_qty_opt)->save();
                }

                $html = array(
                    'success' => '1',
                    'success_message' => $this->_('Settings successfully saved'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                );

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

}