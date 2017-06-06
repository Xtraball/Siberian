<?php
  
class Mcommerce_Application_Settings_ShowcustomercommentController extends Application_Controller_Default_Ajax { 

    public function saveAction() {  

        if($datas = $this->getRequest()->getPost()) {

            try {

                $mcommerce = new Mcommerce_Model_Mcommerce();
                $mcommerce->find(array("value_id" => $datas["option_value_id"]));
                $show_customercomment = $datas["show_customercomment"]?1:0;
                if($mcommerce->getId()) {
                    $mcommerce->setShowCustomercomment($show_customercomment)->save();
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