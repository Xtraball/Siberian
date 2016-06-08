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

    public function postAction() {
        if($datas = Zend_Json::decode($this->getRequest()->getRawBody())) {

            try {

                if((empty($datas['email']) OR empty($datas['password']))) {
                    throw new Exception($this->_('Authentication failed. Please check your email and/or your password'));
                }

                $customer = new Customer_Model_Customer();
                $customer->find(array('email' => $datas['email'], 'app_id' => $this->getApplication()->getId()));

                $password = $datas['password'];

                if(!$customer->getId() OR !$customer->authenticate($password)) {
                    throw new Exception($this->_('Authentication failed. Please check your email and/or your password'));
                }

                //PUSH TO USER ONLY
                if(Push_Model_Message::hasTargetedNotificationsModule()) {
                    if (!empty($datas["device_uid"])) {
                        if (strlen($datas["device_uid"]) == 36) {
                            $device = new Push_Model_Iphone_Device();
                            $device->find($datas["device_uid"], 'device_uid');
                        } else {
                            $device = new Push_Model_Android_Device();

                            if($this->getApplication()->useIonicDesign()) {
                                $device->find($datas["device_uid"], 'device_uid');
                            } else {
                                $device->find($datas["device_uid"], 'registration_id');
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

                $html = array(
                    'success' => 1,
                    'customer_id' => $customer->getId(),
                    'can_access_locked_features' => $customer->canAccessLockedFeatures(),
                    'token' => Zend_Session::getId()
                );

            }
            catch(Exception $e) {
                $html = array('error' => 1, 'message' => $e->getMessage());
            }

            $this->_sendHtml($html);
        }

    }

    public function loginwithfacebookAction() {

        $datas = Zend_Json::decode($this->getRequest()->getRawBody());
        if(isset($datas["token"])) {

            try {

                $access_token = $datas["token"];
                // Reset session
                $this->getSession()->resetInstance();

                $access_token = Core_Model_Lib_Facebook::getOrRefreshToken($access_token);

                if($access_token === false) {
                    throw new Exception($this->_('An error occurred while connecting to your Facebook account. Please try again later'));
                }

                // Fetch data from Facebook
                $graph_url = "https://graph.facebook.com/me?fields=id,name,email,first_name,last_name&access_token=".$access_token;
                $user = json_decode(file_get_contents($graph_url));

                if(!$user instanceof stdClass OR !$user->id) {
                    throw new Exception($this->_('An error occurred while connecting to your Facebook account. Please try again later'));
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

                        // Retrieve its picture from Facebook
                        $social_image = file_get_contents("http://graph.facebook.com/$user_id/picture?type=large");
                        if($social_image) {

                            $formated_name = Core_Model_Lib_String::format($customer->getName(), true);
                            $image_path = $customer->getBaseImagePath().'/'.$formated_name;

                            // Create customer's folder
                            if(!is_dir($customer->getBaseImagePath())) { mkdir($image_path, 0777); }

                            // Store the picture on the server

                            $image_name = uniqid().'.jpg';
                            $image = fopen($image_path.'/'.$image_name, 'w');

                            fputs($image, $social_image);
                            fclose($image);

                            // Resize the image
                            Thumbnailer_CreateThumb::createThumbnail($image_path.'/'.$image_name, $image_path.'/'.$image_name, 150, 150, 'jpg', true);

                            // Set the image to the customer
                            $customer->setImage('/'.$formated_name.'/'.$image_name);
                        }
                    }
                }

                // Set the social data to the customer
                $customer->setSocialData('facebook', array('id' => $user_id, 'datas' => array('access_token' => $access_token)));

                // Save the customer
                $customer->save();

                //PUSH TO USER ONLY
                if(Push_Model_Message::hasTargetedNotificationsModule()) {
                    $device_id = $datas["device_id"];

                    if (!empty($device_id)) {
                        if (strlen($device_id) == 36) {
                            $device = new Push_Model_Iphone_Device();
                            $device->find($device_id, 'device_uid');
                        } else {
                            $device = new Push_Model_Android_Device();

                            if($this->getApplication()->useIonicDesign()) {
                                $device->find($device_id, 'device_uid');
                            } else {
                                $device->find($device_id, 'registration_id');
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
                    'success' => 1,
                    'customer_id' => $customer->getId(),
                    'can_access_locked_features' => $customer->canAccessLockedFeatures(),
                    'token' => Zend_Session::getId()
                );

            }
            catch(Exception $e) {
                $html = array('error' => 1, 'message' => $e->getMessage());
            }

            $this->_sendHtml($html);

        }

    }

    public function logoutAction() {

        $this->getSession()->resetInstance();

        $html = array('success' => 1);

        $this->getLayout()->setHtml(Zend_Json::encode($html));

    }
}
