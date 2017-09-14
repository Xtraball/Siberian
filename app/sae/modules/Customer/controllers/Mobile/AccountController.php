<?php

class Customer_Mobile_AccountController extends Application_Controller_Mobile_Default
{

    public function viewAction() {

        $this->loadPartials($this->getFullActionName('_').'_l'.$this->_layout_id, false);
        $html = array(
            'html' => $this->getLayout()->render(),
            'title' => __('Log-in'),
            'next_button_title' => __('Validate'),
            'next_button_arrow_is_visible' => 1
        );

        $this->getLayout()->setHtml(Zend_Json::encode($html));

    }

    public function avatarAction() {
        $customer_id = $this->getRequest()->getParam("customer");
        $ignore_stored = $this->getRequest()->getParam("ignore_stored") == "true";
        if($customer_id) {
            if($customer_id == "default") {
                $this->_helper->redirector->gotoUrlAndExit("https://www.gravatar.com/avatar/0?s=256&d=mm&r=g&f=y&random=".uniqid(), array("code" => 303));
                exit();
            } else {
                $customer = new Customer_Model_Customer();
                $customer->find($customer_id);
                if($customer->getId()) {
                    $image = $customer->getImage();
                    $path = $customer->getBaseImagePath().$image;
                    if(!($ignore_stored && $customer->getIsCustomImage()) &&
                      (!empty($image) && file_exists($path))) {
                        header("Content-Type: image/jpeg", true, 200);
                        header("Content-Length: ".filesize($path));
                        readfile($path);
                        flush();
                        exit();
                    } else {
                        $email = $customer->getEmail();
                        $this->_helper->redirector->gotoUrlAndExit("https://www.gravatar.com/avatar/".md5($email)."?s=150&d=mm&r=g&random=".uniqid(), array("code" => 303));
                        exit();
                    }
                }
            }
        }
        header("Content-Length: 0", true, 404);
        exit();
    }

    public function editAction() {
        $title = __("Create");
        if($this->getSession()->isLoggedIn('customer')) {
            $title = __("My Account");
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

    public function forgotpasswordAction() {

        $this->loadPartials($this->getFullActionName('_').'_l'.$this->_layout_id, false);
        $html = array(
            'html' => $this->getLayout()->render(),
            'title' => __("Password"),
            'next_button_title' => __('Validate'),
            'next_button_arrow_is_visible' => 1
        );
        $this->getLayout()->setHtml(Zend_Json::encode($html));
    }

    public function loginpostAction() {

        if($datas = $this->getRequest()->getPost()) {

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

            }
            catch(Exception $e) {
                $html = array('error' => 1, 'message' => $e->getMessage());
            }

            $this->_sendJson($html);
        }

    }

    // Not sure if used anywhere
    public function loginwithfacebookAction() {
        if($access_token = $this->getRequest()->getParam('token')) {

            try {

                // Réinitialise la connexion
                $this->getSession()->resetInstance();

                // Récupération des données du compte Facebook
                $graph_url = "https://graph.facebook.com/v2.0/me?access_token=".$access_token;
                $user = json_decode(file_get_contents($graph_url));

                if(!$user instanceof stdClass OR !$user->id) {
                    throw new Exception(__('An error occurred while connecting to your Facebook account. Please try again later'));
                }
                // Récupère le user_id
                $user_id = $user->id;

                // Charge le client à partir du user_id
                $customer = new Customer_Model_Customer();
                $customer->findBySocialId($user_id, 'facebook');

                // Si le client n'a pas de compte
                if(!$customer->getId()) {

                    // Charge le client à partir de l'adresse email afin d'associer les 2 comptes ensemble
                    if($user->email) {
                        $customer->find(array('email' => $user->email));
                    }

                    // Si l'email n'existe pas en base, on crée le client
                    if(!$customer->getId()) {
                        // Préparation des données du client
                        $customer->setData(array(
                            'civility' => $user->gender == 'male' ? 'm' : 'mme',
                            'firstname' => $user->first_name,
                            'lastname' => $user->last_name,
                            'email' => $user->email
                        ));

                        // Ajoute un mot de passe par défaut
                        $customer->setPassword(uniqid());
                    }
                }

                $fbimage = $customer->getImage();
                // Si l'image n'est pas custom (donc est FB) ou si il n'y a pas d'image, on met l'image FB.
                if(!$customer->getIsCustomImage() || empty($fbimage)) {
                    // Récupèration de l'image de Facebook
                    $social_image_json = json_decode(file_get_contents("https://graph.facebook.com/me/picture?redirect=false&type=large&access_token=".$access_token));
                    file_put_contents("/Users/pof/data.txt", var_export($social_image_json, true));
                    if($social_image_json) {
                        if($social_image_json->is_silhouette === false) {
                            $social_image = file_get_contents($social_image_json->url);
                            if($social_image) {

                                $formated_name = Core_Model_Lib_String::format($customer->getName(), true);
                                $image_path = $customer->getBaseImagePath().'/'.$formated_name;

                                // Créer le dossier du client s'il n'existe pas
                                if(!is_dir($customer->getBaseImagePath())) { mkdir($image_path, 0777); }

                                // Créer l'image sur le serveur

                                $image_name = uniqid().'.jpg';
                                $image = fopen($image_path.'/'.$image_name, 'w');

                                fputs($image, $social_image);
                                fclose($image);

                                // Redimensionne l'image
                                Thumbnailer_CreateThumb::createThumbnail($image_path.'/'.$image_name, $image_path.'/'.$image_name, 256, 256, 'jpg', true);

                                // Affecte l'image au client
                                $customer->setImage('/'.$formated_name.'/'.$image_name)->setIsCustomImage(0);

                                // delete old picture
                                if(!empty($fbimage) && file_exists($fbimage))
                                    unlink($fbimage);
                            }
                        } else {
			    $customer_image = $customer->getImage();
                            if(!empty($customer_image) && file_exists($customer->getImage())) {
                                unlink($customer->getImage());
                            }
                            $customer->setImage(NULL)->setIsCustomImage(0);
                        }
                    }
                }

                // Affecte les données du réseau social au client
                $customer->setSocialData('facebook', array('id' => $user_id, 'datas' => $access_token));

                // Sauvegarde du nouveau client
                $customer->save();

                // Connexion du client
                $this->getSession()->setCustomer($customer);

                $html = array(
                    'success' => true,
                    'customer_id' => $customer->getId(),
                    'customer' => $this->_getCustomer()
                );

            }
            catch(Exception $e) {
                $html = array(
                    'error' => true,
                    'message' => $e->getMessage()
                );
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
                $password = Core_Model_Lib_String::generate(8);
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

                if(!$isNew AND !empty($datas['old_password']) AND !$customer->isSamePassword($datas['old_password'])) {
                    throw new Exception(__("The old password does not match the entered password."));
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

    protected function _sendNewAccountEmail($customer, $password) {

        $admin_email = null;
        $contact = new Contact_Model_Contact();
        $contact_page = $this->getApplication()->getPage('contact');
        //$sender = 'no-reply@'.Core_Model_Lib_String::format($this->getApplication()->getName(), true).'.com';

        if($contact_page->getId()) {
            $contact->find($contact_page->getId(), 'value_id');
            $admin_email = $contact->getEmail();
        }

        $layout = $this->getLayout()->loadEmail('customer', 'create_account');
        $layout->getPartial('content_email')->setCustomer($customer)->setPassword($password)->setAdminEmail($admin_email)->setApp($this->getApplication()->getName());
        $content = $layout->render();

        # @version 4.8.7 - SMTP
        $mail = new Siberian_Mail();
        $mail->setBodyHtml($content);
        //$mail->setFrom($sender, $this->getApplication()->getName());
        $mail->addTo($customer->getEmail(), $customer->getName());
        $mail->setSubject(__('%s - Account creation', $this->getApplication()->getName()));
        $mail->send();

        return $this;

    }

    /**
     * @return array
     */
    private function _getCustomer() {
        return Customer_Model_Customer::getCurrent();
    }


}
