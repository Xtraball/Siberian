<?php

use Siberian\Account;
use Siberian\Layout;

/**
 * Class Customer_Mobile_AccountController
 */
class Customer_Mobile_AccountController extends Application_Controller_Mobile_Default
{
    /**
     *
     */
    public function viewAction()
    {
        $this->loadPartials($this->getFullActionName('_').'_l'.$this->_layout_id, false);
        $payload = [
            'html' => $this->getLayout()->render(),
            'title' => __('Log-in'),
            'next_button_title' => __('Validate'),
            'next_button_arrow_is_visible' => 1
        ];

        $this->_sendJson($payload);
    }

    /**
     * @throws Zend_Controller_Response_Exception
     * @throws Zend_Exception
     */
    public function avatarAction()
    {
        $request = $this->getRequest();
        $customerId = $request->getParam('customer', false);
        $ignoreStored = $request->getParam('ignore_stored', false) === 'true';
        $returnJson = filter_var($request->getParam('json', false), FILTER_VALIDATE_BOOLEAN);
        try {
            if (!$customerId) {
                throw new \Siberian\Exception('https://www.gravatar.com/avatar/0?s=256&d=mm&r=g&f=y&random=' . uniqid('rng', true));
            }

            if ($customerId === 'default') {
                throw new \Siberian\Exception('https://www.gravatar.com/avatar/0?s=256&d=mm&r=g&f=y&random=' . uniqid('rng', true));
            }

            if (is_numeric($customerId)) {
                /**
                 * @var $customer Customer_Model_Customer
                 */
                $customer = (new Customer_Model_Customer())->find($customerId);
                if ($customer && $customer->getId()) {
                    $image = $customer->getImage();
                    $path = $customer->getImagePath() . $image;
                    $basePath = $customer->getBaseImagePath() . $image;

                    // Ok user has custom avatar!
                    if (
                        !($ignoreStored && $customer->getIsCustomImage()) &&
                        (!empty($image) && is_readable($basePath))) {
                        throw new \Siberian\Exception($path);
                    }

                    $email = md5($customer->getEmail());
                    throw new \Siberian\Exception('https://www.gravatar.com/avatar/'. $email . '?s=256&d=mm&r=g&f=y&random=' . uniqid('rng', true));
                }
            }

        } catch (\Exception $e) {
            if (!$returnJson) {
                $this->_helper->redirector->gotoUrlAndExit($e->getMessage(), ['code' => 302]);
                exit();
            }
            $this->_sendJson([
                'success' => true,
                'url' => $request->getBaseUrl() . $e->getMessage()
            ]);
        }
    }

    /**
     *
     */
    public function editAction()
    {
        $title = __("Create");
        if($this->getSession()->isLoggedIn('customer')) {
            $title = __("My account");
        }

        $this->loadPartials($this->getFullActionName('_').'_l'.$this->_layout_id, false);
        $html = array(
            'html' => $this->getLayout()->render(),
            'title' => $title,
            'next_button_title' => __('Validate'),
            'next_button_arrow_is_visible' => 1
        );
        $this->getLayout()->setHtml(Zend_Json::encode($html));
    }

    /**
     *
     */
    public function forgotpasswordAction()
    {
        $this->loadPartials($this->getFullActionName('_').'_l'.$this->_layout_id, false);
        $html = array(
            'html' => $this->getLayout()->render(),
            'title' => __("Password"),
            'next_button_title' => __('Validate'),
            'next_button_arrow_is_visible' => 1
        );
        $this->getLayout()->setHtml(Zend_Json::encode($html));
    }

    /**
     *
     */
    public function loginpostAction()
    {
        if ($datas = $this->getRequest()->getPost()) {
            try {
                if((empty($datas['email']) OR empty($datas['password']))) {
                    throw new Exception(__('Authentication failed. Please check your email and/or your password'));
                }

                $customer = new Customer_Model_Customer();
                $customer->find($datas['email'], 'email');
                $password = $datas['password'];

                if(!$customer->authenticate($password)) {
                    throw new Exception(__('Authentication failed. Please check your email and/or your password'));
                }

                $this->getSession()
                    ->resetInstance()
                    ->setCustomer($customer)
                ;

                $html = array('success' => 1, 'customer_id' => $customer->getId());

            } catch (Exception $e) {
                $html = array('error' => 1, 'message' => $e->getMessage());
            }
            $this->_sendJson($html);
        }
    }

    public function forgotpasswordpostAction() {

        if($datas = $this->getRequest()->getPost() AND !$this->getSession()->isLoggedIn('customer')) {

            try {

                if(empty($datas['email'])) throw new Exception(__('Please enter your email address'));
                if(!Zend_Validate::is($datas['email'], 'EmailAddress')) throw new Exception(__('Please enter a valid email address'));

                $customer = new Customer_Model_Customer();
                $customer->find($datas['email'], 'email');

                if(!$customer->getId()) {
                    throw new Exception("Your email address does not exist");
                }

                $admin_email = null;
                $password = generate_strong_password(10);
                $contact = new Contact_Model_Contact();
                $contact_page = $this->getApplication()->getPage('contact');
                if($contact_page->getId()) {
                    $contact->find($contact_page->getId(), 'value_id');
                    $admin_email = $contact->getEmail();
                }

                $customer->setPassword($password)->save();

                //$sender = 'no-reply@'.Core_Model_Lib_String::format($this->getApplication()->getName(), true).'.com';
                $layout = $this->getLayout()->loadEmail('customer', 'forgot_password');
                $layout->getPartial('content_email')->setCustomer($customer)->setPassword($password)->setAdminEmail($admin_email)->setApp($this->getApplication()->getName());
                $content = $layout->render();

                # @version 4.8.7 - SMTP
                $mail = new Siberian_Mail();
                $mail->setBodyHtml($content);
                //$mail->setFrom($sender, $this->getApplication()->getName());
                $mail->addTo($customer->getEmail(), $customer->getName());
                $mail->setSubject(__('%s – Your new password', $this->getApplication()->getName()));
                $mail->send();

                $html = array('success' => 1);

                $html['message_success'] = __("Your new password has been sent to the entered email address");

            }
            catch(Exception $e) {
                $html = array(
                    'error' => 1,
                    'message' => $e->getMessage()
                );
            }

            $this->_sendJson($html);

        }

        return $this;

    }

    public function savepostAction() {

        if($datas = $this->getRequest()->getPost()) {

            if(!$customer = $this->getSession()->getCustomer()) {
                $customer = new Customer_Model_Customer();
            }
            $isNew = !$customer->getId();
            $isMobile = APPLICATION_TYPE == 'mobile';

            try {

                if(!Zend_Validate::is($datas['email'], 'EmailAddress')) {
                    throw new Exception(__('Please enter a valid email address'));
                }
                $dummy = new Customer_Model_Customer();
                $dummy->find($datas['email'], 'email');

                if($isNew AND $dummy->getId()) {
                    throw new Exception(__('We are sorry but this address is already used.'));
                }

                if(!empty($datas['social_datas'])) {
                    $social_ids = array();
                    foreach($datas['social_datas'] as $type => $data) {
                        if($customer->findBySocialId($data['id'], $type)->getId()) {
                            throw new Exception(__('We are sorry but the %s account is already linked to one of our customers', ucfirst($type)));
                        }
                        $social_ids[$type] = array('id' => $data['id']);
                    }
                }
                $password = $customer->getPassword();
                if(empty($datas['show_in_social_gaming'])) {
                    $datas['show_in_social_gaming'] = 0;
                }

                $customer->setData($datas);
                $customer->setData('password', $password);

                if(isset($datas['id']) AND $datas['id'] != $this->getSession()->getCustomer()->getId()) {
                    throw new Exception(__('An error occurred while saving. Please try again later.'));
                }

                $formated_name = Core_Model_Lib_String::format($customer->getName(), true);
                $base_logo_path = $customer->getBaseImagePath().'/'.$formated_name;

                if($customer->getSocialPicture()) {
                    $social_image = file_get_contents($customer->getSocialPicture());
                    if($social_image) {
                        if(!is_dir($customer->getBaseImagePath())) { mkdir($customer->getBaseImagePath(), 0777); }

                        $image_name = uniqid().'.jpg';
                        $image = fopen($customer->getBaseImagePath().'/'.$image_name, 'w');
                        fputs($image, $social_image);
                        fclose($image);

                        $customer->setImage('/'.$formated_name.'/'.$image_name);
                    }
                    else {
                        $this->getSession()->addError(__('An error occurred while saving your picture. Please try againg later.'));
                    }
                }

                if(empty($datas['password']) AND $isNew) {
                    throw new Exception(__('Please enter a password'));
                }

                if(!$isMobile AND $datas['password'] != $datas['confirm_password']) {
                    throw new Exception(__('Your password does not match the entered password.'));
                }

                if($isNew AND !$isMobile AND $datas['email'] != $datas['confirm_email']) {
                    throw new Exception(__("The old email address does not match the entered email address."));
                }

                if (!$isNew &&
                    !empty($datas['old_password']) &&
                    !$customer->isSamePassword($datas['old_password'])) {
                    throw new \Exception(p__('customer', 'The actual password is incorrect.'));
                }

                if(!empty($datas['password'])) $customer->setPassword($datas['password']);

                if(!empty($social_ids)) $customer->setSocialDatas($social_ids);

                $customer->save();

                $this->getSession()->setCustomer($customer);

                if($isNew) {
                    $this->_sendNewAccountEmail($customer, $datas['password']);
                }

                if(!$isMobile) {

                    $this->getSession()->addSuccess(__('Your account has been successfully saved'));

                    // Retour des données (redirection vers la page en cours)
                    $referer = !empty($datas['referer']) ? $datas['referer'] : $this->getRequest()->getHeader('referer');
                    $this->_redirect($referer);
                    return $this;
                }

                foreach($this->getRequest()->getParam('add_to_session', array()) as $key => $value) {
                    $this->getSession()->$key = $value;
                }

                $html = array(
                    'success' => 1,
                    'customer_id' => $customer->getId(),
                    'customer' => $this->_getCustomer()
                );

            }
            catch(Exception $e) {
                $html = array(
                    'error' => 1,
                    'message' => $e->getMessage()
                );
            }

            $this->_sendJson($html);

        }

    }

    public function logoutAction() {

        $this->getSession()->resetInstance();

        $redirect = urldecode($this->getRequest()->getParam('redirect_url', $this->getRequest()->getHeader('referer')));
        $html = array('success' => 1, 'redirect' => $redirect);

        $this->getLayout()->setHtml(Zend_Json::encode($html));

    }

    public function requestTokenAction ()
    {
        try {
            $session = $this->getSession();
            if (!$session->isLoggedIn()) {
                throw new Siberian_Exception(__('You must be logged-in to request a new token.'));
            }

            $customer = $session->getCustomer();

            $newToken = uniqid('tk_', true);
            $customer
                ->setGdprToken($newToken)
                ->save();

            $host = __get('main_domain');
            $whitelabel = Siberian::getWhitelabel();
            if ($whitelabel !== false) {
                $wlHost = $whitelabel->getHost();
                $wlHost = trim($wlHost);
                if (!empty($wlHost)) {
                    $host = $wlHost;
                }
            }

            $url = sprintf('https://%s/%s?token=%s', $host, 'customer/account/mydata', $newToken);

            try {
                // E-Mail back the user!
                $application = $this->getApplication();
                $applicationName = $application->getName();

                $subject = __("%s - Access to your personal data", $applicationName);

                $baseEmail = $this->baseEmail("gdpr_token", $subject, "", false);

                $data = [
                    "customer" => $customer,
                    "gdpr_token" => $newToken,
                    "url" => $url,
                    "app" => $applicationName,
                ];
                foreach ($data as $key => $value) {
                    $baseEmail->setContentFor('content_email', $key, $value);
                }

                $content = $baseEmail->render();

                $mail = new \Siberian_Mail();
                $mail->setBodyHtml($content);
                $mail->addTo($customer->getEmail(), $customer->getName());
                $mail->setSubject($subject);
                $mail->send();
            } catch (\Exception $e) {
                // Something went wrong with the-mail!
            }

            $payload = [
                'success' => true,
                'message' => __("We've sent you an e-mail with your access token.")
            ];

        } catch (Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     * @param $nodeName
     * @param $title
     * @param $message
     * @param $showLegals
     * @return Siberian_Layout|Siberian_Layout_Email
     * @throws Zend_Layout_Exception
     */
    public function baseEmail($nodeName,
                              $title,
                              $message = '',
                              $showLegals = false)
    {
        $layout = new Siberian\Layout();
        $layout = $layout->loadEmail('customer', $nodeName);
        $layout
            ->setContentFor('base', 'email_title', $title)
            ->setContentFor('content_email', 'message', $message)
            ->setContentFor('footer', 'show_legals', $showLegals);

        return $layout;
    }

    ///**
    // * @todo sender is contact if feature exists
    // *
    // * @param $customer
    // * @param $password
    // * @return $this
    // */
    //protected function _sendNewAccountEmail($customer, $password) {
//
    //    $admin_email = null;
    //    $contact = new Contact_Model_Contact();
    //    $contact_page = $this->getApplication()->getPage('contact');
    //    //$sender = 'no-reply@'.Core_Model_Lib_String::format($this->getApplication()->getName(), true).'.com';
//
    //    if($contact_page->getId()) {
    //        $contact->find($contact_page->getId(), 'value_id');
    //        $admin_email = $contact->getEmail();
    //    }
//
    //    $layout = $this->getLayout()->loadEmail('customer', 'create_account');
    //    $layout->getPartial('content_email')->setCustomer($customer)->setPassword($password)->setAdminEmail($admin_email)->setApp($this->getApplication()->getName());
    //    $content = $layout->render();
//
    //    # @version 4.8.7 - SMTP
    //    $mail = new Siberian_Mail();
    //    $mail->setBodyHtml($content);
    //    //$mail->setFrom($sender, $this->getApplication()->getName());
    //    $mail->addTo($customer->getEmail(), $customer->getName());
    //    $mail->setSubject(__('%s - Account creation', $this->getApplication()->getName()));
    //    $mail->send();
//
    //    return $this;
//
    //}

    /**
     * @return array
     * @throws Zend_Session_Exception
     */
    private function _getCustomer() {
        $payload = Customer_Model_Customer::getCurrent();

        $payload["extendedFields"] = Account::getFields([
            "application" => $this->getApplication(),
            "request" => $this->getRequest(),
            "session" => $this->getSession(),
        ]);

        return $payload;
    }

}
