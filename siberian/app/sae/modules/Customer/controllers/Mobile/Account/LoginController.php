<?php

class Customer_Mobile_Account_LoginController extends Application_Controller_Mobile_Default
{

    public function indexAction() {
        if($this->getSession()->isLoggedIn()) {
            $this->_redirect("customer/mobile_account_edit");
        } else {
            parent::indexAction();
        }
    }

    public function postv2Action() {

        try {

            $request = $this->getRequest();

            if($params = Siberian_Json::decode($request->getRawBody())) {


            } else {
                throw new Siberian_Exception(__("Missing parameters."));
            }

        } catch(Exception $e) {

            $payload = array(
                "error"     => true,
                "message"   => $e->getMessage()
            );

        }

        $this->_sendJson($payload);
    }

    private function _getCustomer() {
        return Customer_Model_Customer::getCurrent();
    }

    public function postAction() {
        try {

            if($datas = Siberian_Json::decode($this->getRequest()->getRawBody())) {

                if((empty($datas['email']) OR empty($datas['password']))) {
                    throw new Siberian_Exception(
                        __('Authentication failed. Please check your email and/or your password')
                    );
                }

                $customer = new Customer_Model_Customer();
                $customer->find(array(
                    "email" => $datas['email'],
                    "app_id" => $this->getApplication()->getId()
                ));

                $password = $datas['password'];

                if(!$customer->getId() OR !$customer->authenticate($password)) {
                    throw new Siberian_Exception(
                        __('Authentication failed. Please check your email and/or your password')
                    );
                }

                //PUSH TO USER ONLY
                if(Push_Model_Message::hasIndividualPush()) {
                    if (!empty($datas["device_uid"])) {
                        if (strlen($datas["device_uid"]) == 36) {
                            $device = new Push_Model_Iphone_Device();
                            $device->find(array(
                                "device_uid" => $datas["device_uid"],
                                "app_id" => $this->getApplication()->getId(),
                            ));
                        } else {
                            $device = new Push_Model_Android_Device();

                            if($this->getApplication()->useIonicDesign()) {
                                $device->find(array(
                                    "device_uid" => $datas["device_uid"],
                                    "app_id" => $this->getApplication()->getId(),
                                ));
                            } else {
                                $device->find(array(
                                    "registration_id" => $datas["device_uid"],
                                    "app_id" => $this->getApplication()->getId(),
                                ));
                            }
                        }

                        if ($device->getId() && !$device->getCustomerId()) {
                            $device->setCustomerId($customer->getId())->save();
                        }
                    }
                }

                if(!$customer->getAppId()) {
                    $customer->setAppId($this->getApplication()->getId())->save();
                }

                $this->getSession()
                    ->resetInstance()
                    ->setCustomer($customer)
                ;

                $payload = array(
                    "success" => true,
                    "customer_id" => $customer->getId(),
                    "can_access_locked_features" => $customer->canAccessLockedFeatures(),
                    "token" => Zend_Session::getId(),
                    "customer" => $this->_getCustomer()
                );

            } else {
                throw new Siberian_Exception(__("An error occurred, please try again."));
            }

        } catch(Exception $e) {
            $payload = array(
                "error"     => true,
                "message"   => $e->getMessage()
            );
        }

        $this->_sendJson($payload);

    }

    public function loginwithfacebookAction() {
        $datas = Siberian_Json::decode($this->getRequest()->getRawBody());
        if(isset($datas["token"])) {

            try {

                $access_token = $datas["token"];
                // Reset session
                $this->getSession()->resetInstance();

                $access_token = Core_Model_Lib_Facebook::getOrRefreshToken($access_token);

                if($access_token === false) {
                    throw new Siberian_Exception(__('An error occurred while connecting to your Facebook account. Please try again later'));
                }

                // Fetch data from Facebook
                $graph_url = "https://graph.facebook.com/me?fields=id,name,email,first_name,last_name&access_token=".$access_token;
                $user = json_decode(file_get_contents($graph_url));

                if(!$user instanceof stdClass OR !$user->id) {
                    throw new Siberian_Exception(__('An error occurred while connecting to your Facebook account. Please try again later'));
                }
                // Retrieve the user_id
                $user_id = $user->id;

                // Retrieve the current app ID
                $app_id = $this->getApplication()->getId();

                // Load the customer from the user_id
                $customer = new Customer_Model_Customer();
                $customer->findBySocialId($user_id, 'facebook', $app_id);

                // If the customer doesn't exist
                if(!$customer->getId()) {

                    // Load the customer based on the email address in order to link the 2 accounts together
                    if($user->email) {
                        $customer->find(array("email" => $user->email, "app_id" => $app_id));
                    }

                    // If the email doesn't exist, create the account
                    if(!$customer->getId()) {
                        // Préparation des données du client
                        $customer->setData(array(
                            "app_id" => $app_id,
                            // "civility" => $user->gender == "male" ? "m" : "mme", // Is not sent back anymore
                            "firstname" => $user->first_name,
                            "lastname" => $user->last_name,
                            "email" => $user->email
                        ));

                        // Add a default password
                        $customer->setPassword(uniqid());
                    }
                }

                $fbimage = $customer->getImage();
                // Si l'image n'est pas custom (donc est FB) ou si il n'y a pas d'image, on met l'image FB.
                if(!$customer->getIsCustomImage() || empty($fbimage)) {
                    // Récupèration de l'image de Facebook
                    $social_image_json = json_decode(file_get_contents("https://graph.facebook.com/v2.0/me/picture?redirect=false&type=large&access_token=".$access_token));
                    if($social_image_json && $social_image_json->data) {
                        if($social_image_json->data->is_silhouette === false) {
                            $social_image = file_get_contents($social_image_json->data->url);
                            if($social_image) {
                                $formated_name = md5($customer->getId());
                                $image_path = $customer->getBaseImagePath().'/'.$formated_name;

                                // Create customer's folder
                                if(!is_dir($image_path)) { mkdir($image_path, 0777, true); }

                                // Store the picture on the server

                                $image_name = uniqid().'.jpg';
                                $image = fopen($image_path.'/'.$image_name, 'w');

                                fputs($image, $social_image);
                                fclose($image);

                                // Resize the image
                                Thumbnailer_CreateThumb::createThumbnail($image_path.'/'.$image_name, $image_path.'/'.$image_name, 256, 256, 'jpg', true);

                                // Set the image to the customer
                                $customer->setImage('/'.$formated_name.'/'.$image_name)->setIsCustomImage(0);
                            }
                        } else {
                            $customer->setImage(NULL)->setIsCustomImage(0);
                        }

                        // delete old picture
                        if(!empty($fbimage) && $fbimage != $customer->getImage() && file_exists($customer->getBaseImagePath().$fbimage))
                            unlink($customer->getBaseImagePath().$fbimage);
                    }
                }

                // Set the social data to the customer
                $customer->setSocialData('facebook', array('id' => $user_id, 'datas' => array('access_token' => $access_token)));

                // Save the customer
                $customer->save();

                //PUSH TO USER ONLY
                if(Push_Model_Message::hasIndividualPush()) {
                    $device_id = $datas["device_id"];

                    if (!empty($device_id)) {
                        if (strlen($device_id) == 36) {
                            $device = new Push_Model_Iphone_Device();
                            $device->find(array(
                                "device_uid" => $device_id,
                                "app_id" => $this->getApplication()->getId(),
                            ));
                        } else {
                            $device = new Push_Model_Android_Device();

                            if($this->getApplication()->useIonicDesign()) {
                                $device->find(array(
                                    "device_uid" => $device_id,
                                    "app_id" => $this->getApplication()->getId(),
                                ));
                            } else {
                                $device->find(array(
                                    "registration_id" => $device_id,
                                    "app_id" => $this->getApplication()->getId(),
                                ));
                            }
                        }

                        if ($device->getId() && !$device->getCustomerId()) {
                            $device->setCustomerId($customer->getId())->save();
                        }
                    }
                }

                // Log-in the customer
                $this->getSession()->setCustomer($customer);

                $html = array(
                    'success' => true,
                    'customer_id' => $customer->getId(),
                    'can_access_locked_features' => $customer->canAccessLockedFeatures(),
                    'token' => Zend_Session::getId(),
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

    public function logoutAction() {

        /** Unlink from individual push */
        if(Push_Model_Message::hasIndividualPush()) {
            $customer_id = $this->getSession()->getCustomerId();

            $model_ios = new Push_Model_Iphone_Device();
            $device_ios = $model_ios->findAll(array(
                "customer_id = ?" => $customer_id,
                "app_id = ?" => $this->getApplication()->getId(),
            ));

            foreach($device_ios as $ios) {
                $ios->setCustomerId(null)->save();
            }

            $model_android = new Push_Model_Android_Device();
            $device_android = $model_android->findAll(array(
                "customer_id = ?" => $customer_id,
                "app_id = ?" => $this->getApplication()->getId(),
            ));

            foreach($device_android as $android) {
                $android->setCustomerId(null)->save();
            }

        }

        $this->getSession()->resetInstance();

        $html = array('success' => 1);

        $this->getLayout()->setHtml(Zend_Json::encode($html));

    }
}
