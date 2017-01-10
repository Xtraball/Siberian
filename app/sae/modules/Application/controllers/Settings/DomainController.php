<?php

class Application_Settings_DomainController extends Application_Controller_Default {

    public function indexAction() {
        $this->loadPartials();
    }

    public function saveAction() {

        if($data = $this->getRequest()->getPost()) {

            try {

                if(!empty($data['domain'])) {
                    $data['domain'] = trim(str_replace(array('https', 'http', '://'), '', $data['domain']));
                    $parts = explode('/', $data['domain']);
                    $data['domain'] = !empty($parts[0]) ? $parts[0] : null;
                    $url = 'http://'.$data['domain'];

                    $domain_url = trim(str_replace(array('https', 'http', '://'), '', $this->getRequest()->getBaseUrl()));

                    if(!Zend_Uri::check($url)) {
                        throw new Exception(__('Please enter a valid address'));
                    } else if(preg_match('/^(www.)?('.$data['domain'].')/', $domain_url)) {
                        throw new Exception(__("You can't use this domain."));
                    }

                    $domain_folder = $parts[1];
                    $module_names = array_map('strtolower', Zend_Controller_Front::getInstance()->getDispatcher()->getSortedModuleDirectories());
                    if(in_array($domain_folder, $module_names)) {
                        throw new Exception(__("Your domain key \"%s\" is not valid.", $domain_folder));
                    }

                    if(!Core_Model_Url::checkCname($data["domain"])) {
                        throw new Exception(__("Your CNAME is not properly set"));
                    }
    
                } else {
                    $data['domain'] = null;
                }

                $this->getApplication()
                     ->setDomain($data['domain'])
                     ->save()
                ;

                $html = array(
                    'success' => '1',
                    'success_message' => __('Info successfully saved'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0,
                    'domain' => $this->getApplication()->getDomain(),
                    'show_cname_state' => !empty($data["domain"]),
                    'application_url' => $this->getApplication()->getUrl()
                );

            }
            catch(Exception $e) {
                $html = array('message' => $e->getMessage());
            }

            $this->_sendHtml($html);
        }

    }

    public function checkcnameAction() {

        if($this->getRequest()->isPost()) {

            try {

                $code = 1;
                $application = $this->getApplication();
                if($application->getDomain() AND Core_Model_Url::checkCname($application->getDomain())) {
                    $code = 0;
                }

            }
            catch(Exception $e) {
                $code = 1;
            }
            $html = Zend_Json::encode(array('code' => $code));
            $this->getLayout()->setHtml($html);
        }

    }


}
