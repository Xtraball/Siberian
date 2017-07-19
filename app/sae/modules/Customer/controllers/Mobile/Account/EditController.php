<?php

class Customer_Mobile_Account_EditController extends Application_Controller_Mobile_Default {

    public function findAction() {

        $customer = $this->getSession()->getCustomer();
        $payload = array();
        $payload["is_logged_in"] = false;

        if($customer->getId()) {
            $metadatas = $customer->getMetadatas();
            if(empty($metadatas)) {
                $metadatas = json_decode("{}"); // we really need a javascript object here
            }

            //hide stripe customer id for secure purpose
            if($metadatas->stripe && array_key_exists("customerId",$metadatas->stripe) && $metadatas->stripe["customerId"]) {
                unset($metadatas->stripe["customerId"]);
            }

            $payload = array(
                "id" => $customer->getId(),
                "civility" => $customer->getCivility(),
                "firstname" => $customer->getFirstname(),
                "lastname" => $customer->getLastname(),
                "nickname" => $customer->getNickname(),
                "email" => $customer->getEmail(),
                "show_in_social_gaming" => (bool) $customer->getShowInSocialGaming(),
                "is_custom_image" => (bool) $customer->getIsCustomImage(),
                "metadatas" => $metadatas
            );

            if(Siberian_CustomerInformation::isRegistered("stripe")) {
                $exporter_class = Siberian_CustomerInformation::getClass("stripe");
                if(class_exists($exporter_class) && method_exists($exporter_class, "getInformation")) {
                    $tmp_class = new $exporter_class();
                    $info = $tmp_class->getInformation($customer->getId());
                    $payload["stripe"] = $info ? $info : array();
                }
            }

            $payload["is_logged_in"] = true;

        }

        $this->_sendJson($payload);

    }

    public function postAction() {

        if($data = Zend_Json::decode($this->getRequest()->getRawBody())) {

            $customer = $this->getSession()->getCustomer();

            try {

                $clearCache = false;

                if(!$customer->getId()) {
                    throw new Exception($this->_("An error occurred while saving. Please try again later."));
                }

                if(!Zend_Validate::is($data['email'], 'EmailAddress')) {
                    throw new Exception($this->_('Please enter a valid email address'));
                }

                $dummy = new Customer_Model_Customer();
                $dummy->find(array('email' => $data['email'], "app_id" => $this->getApplication()->getId()));

                if($dummy->getId() AND $dummy->getId() != $customer->getId()) {
                    throw new Exception($this->_('We are sorry but this address is already used.'));
                }

                if(!empty($data["nickname"])) {
                    $valid_format = preg_match("/^[A-Za-z0-9_]{1,15}$/", $data["nickname"]);
                    if(!$valid_format) {
                        throw new Exception($this->_('We are sorry but this nickname is not valid. Use only alphanumerical characters and underscores and use 15 characters maximum'));
                    }

                    $dummy = new Customer_Model_Customer();
                    $dummy->find(array('nickname' => $data['nickname'], "app_id" => $this->getApplication()->getId()));

                    if($dummy->getId() AND $dummy->getId() != $customer->getId()) {
                        throw new Exception($this->_('We are sorry but this nickname is already used.'));
                    }
                }

                if(empty($data['show_in_social_gaming'])) $data['show_in_social_gaming'] = 0;

                if($data['show_in_social_gaming'] != $customer->getShowInSocialGaming()) $clearCache = true;

                if(isset($data['id'])) unset($data['id']);
                if(isset($data['customer_id'])) unset($data['customer_id']);

                if($data['delete_avatar'] === true) {
                    $path = $customer->getFullImagePath();
                    if($path) {
                        $customer->setImage(NULL)->setIsCustomImage(0)->save();
                        $data['image'] = null;
                        $data['is_custom_image'] = 0;
                        unlink($path);
                    }
                } elseif ( !empty($data['avatar']) ) {
                    $formated_name = md5($customer->getId());
                    $image_path = $customer->getBaseImagePath().'/'.$formated_name;

                    // Create customer's folder
                    if(!is_dir($image_path)) { mkdir($image_path, 0777, true); }

                    // Store the picture on the server
                    $image_name = uniqid().'.jpg';
                    $newavatar = base64_decode(str_replace(' ', '+', preg_replace('#^data:image/\w+;base64,#i', '', $data['avatar'])));
                    $file = fopen($image_path."/".$image_name, "wb");
                    fwrite($file, $newavatar);
                    fclose($file);

                    // Resize the image
                    Thumbnailer_CreateThumb::createThumbnail($image_path.'/'.$image_name, $image_path.'/'.$image_name, 256, 256, 'jpg', true);

                    $oldImage = $customer->getFullImagePath();

                    // Set the image to the customer
                    $customer->setImage('/'.$formated_name.'/'.$image_name)->setIsCustomImage(1)->save();
                    $data['image'] = '/'.$formated_name.'/'.$image_name;
                    $data['is_custom_image'] = 1;

                    if($oldImage) {
                        unlink($oldImage);
                    }
                }

                $password = "";
                if(!empty($data['password'])) {

                    if(empty($data['old_password']) OR (!empty($data['old_password']) AND !$customer->isSamePassword($data['old_password']))) {
                        throw new Exception($this->_("The old password does not match the entered password."));
                    }

                    $password = $data['password'];
                }

                $customer->setData($data);
                if(!empty($password)) $customer->setPassword($password);
                if(!empty($data["metadatas"])) $customer->setMetadatas($data["metadatas"]);
                $customer->save();

                $html = array(
                    "success" => 1,
                    "message" => $this->_("Info successfully saved"),
                    "clearCache" => $clearCache,
                    "customer" => Customer_Model_Customer::getCurrent()
                );

            }
            catch(Exception $e) {
                $html = array('error' => 1, 'message' => $e->getMessage());
            }

            $this->_sendHtml($html);

        }

    }

}
