<?php

class Application_Customization_PreviewController extends Application_Controller_Default {

    public function indexAction() {
        $this->loadPartials();
    }

    public function saveAction() {

        if($datas = $this->getRequest()->getPost()) {

            $html = array();
            try {
                if(empty($datas['subdomain'])) throw new Exception(__('Please enter a valid mobile website address.'));
                if(preg_match("#[^a-z0-9]#", $datas['subdomain'])) throw new Exception(__('Your mobile address should not contain special characters'));
                
                $this->getApplication()
                    ->setSubdomain($datas['subdomain'])
                    ->setSubdomainIsValidated(1)
                    ->save()
                ;

                $html = array(
                    'success' => 1,
                    'qrcode' => $this->getApplication()->getQrcode(null, array('size' => '200x200', 'without_template' => 1))
                );
            }
            catch(Exception $e) {
                $html['message'] = $e->getMessage();
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

}
