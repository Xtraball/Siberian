<?php

class Application_Customization_Publication_WebsiteController extends Admin_Controller_Default {

    public function indexAction() {
        $this->getLayout()->setBaseRender('content', 'application/customization/publication/website.phtml', 'admin_view_default');
        $html = array('html' => $this->getLayout()->render());
        $this->getLayout()->setHtml(Zend_Json::encode($html));

    }

    public function saveAction() {

        if($datas = $this->getRequest()->getPost()) {

            try {

                $message = '';

                // Récupère le commerçe en cours
                $admin = $this->getSession()->getAdmin();
                if(empty($datas['url_key'])) throw new Exception(__('Please enter a subdomain.'));
                if(preg_match("#[^a-z0-9]#", $datas['url_key'])) throw new Exception(__('Your mobile address should not contain special characters'));

                $dummy = new Admin_Model_Admin();
                if($dummy->find($datas['url_key'], 'url_key')->getId()) {
                    throw new Exception(__('We are sorry but this address is already used.'));
                }

                $admin->setUrlKey($datas['url_key'])->save();

                $html = array(
                    'success' => '1',
                    'success_message' => $message,
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                );

            }
            catch(Exception $e) {
                $html = array('message' => $e->getMessage());
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }

    }

}

?>
