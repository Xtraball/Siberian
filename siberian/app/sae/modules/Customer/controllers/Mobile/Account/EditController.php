<?php

use Siberian\Account;
use Siberian\Hook;
use Siberian\Exception;

/**
 * Class Customer_Mobile_Account_EditController
 */
class Customer_Mobile_Account_EditController extends Application_Controller_Mobile_Default
{
    /**
     * @throws Zend_Session_Exception
     * @throws \rock\sanitize\SanitizeException
     */
    public function findAction()
    {

        $customer = $this->getSession()->getCustomer();
        $payload = [];
        $payload["is_logged_in"] = false;

        if ($customer->getId()) {
            $metadatas = $customer->getMetadatas();
            if (empty($metadatas)) {
                $metadatas = json_decode("{}"); // we really need a javascript object here
            }

            //hide stripe customer id for secure purpose
            if ($metadatas->stripe && array_key_exists("customerId", $metadatas->stripe) && $metadatas->stripe["customerId"]) {
                unset($metadatas->stripe["customerId"]);
            }

            $payload = [
                "id" => $customer->getId(),
                "civility" => $customer->getCivility(),
                "firstname" => $customer->getFirstname(),
                "lastname" => $customer->getLastname(),
                "nickname" => $customer->getNickname(),
                "email" => $customer->getEmail(),
                "show_in_social_gaming" => (bool)$customer->getShowInSocialGaming(),
                "is_custom_image" => (bool)$customer->getIsCustomImage(),
                "metadatas" => $metadatas
            ];

            if (Siberian_CustomerInformation::isRegistered("stripe")) {
                $exporter_class = Siberian_CustomerInformation::getClass("stripe");
                if (class_exists($exporter_class) && method_exists($exporter_class, "getInformation")) {
                    $tmp_class = new $exporter_class();
                    $info = $tmp_class->getInformation($customer->getId());
                    $payload["stripe"] = $info ? $info : [];
                }
            }

            $payload["is_logged_in"] = true;
            $payload["isLoggedIn"] = true;

        }

        $this->_sendJson($payload);

    }

    /**
     * @throws Zend_Json_Exception
     * @throws Zend_Session_Exception
     * @throws Zend_Validate_Exception
     */
    public function postAction()
    {
        if ($data = Zend_Json::decode($this->getRequest()->getRawBody())) {

            $customer = $this->getSession()->getCustomer();

            try {

                $clearCache = false;

                if (!$customer->getId()) {
                    throw new Exception(__("An error occurred while saving. Please try again later."));
                }

                if (!Zend_Validate::is($data['email'], 'EmailAddress')) {
                    throw new Exception(__('Please enter a valid email address'));
                }

                $dummy = new Customer_Model_Customer();
                $dummy->find(['email' => $data['email'], "app_id" => $this->getApplication()->getId()]);

                if ($dummy->getId() AND $dummy->getId() != $customer->getId()) {
                    throw new Exception(__('We are sorry but this address is already used.'));
                }

                if (!empty($data["nickname"])) {
                    $valid_format = preg_match("/^[A-Za-z0-9_]{1,15}$/", $data["nickname"]);
                    if (!$valid_format) {
                        throw new Exception(__('We are sorry but this nickname is not valid. Use only alphanumerical characters and underscores and use 15 characters maximum'));
                    }

                    $dummy = new Customer_Model_Customer();
                    $dummy->find(['nickname' => $data['nickname'], "app_id" => $this->getApplication()->getId()]);

                    if ($dummy->getId() && $dummy->getId() != $customer->getId()) {
                        throw new Exception(__('We are sorry but this nickname is already used.'));
                    }
                }

                if (empty($data['show_in_social_gaming'])) {
                    $data['show_in_social_gaming'] = 0;
                }

                if ($data['show_in_social_gaming'] != $customer->getShowInSocialGaming()) {
                    $clearCache = true;
                }

                if (isset($data['id'])) {
                    unset($data['id']);
                }
                if (isset($data['customer_id'])) {
                    unset($data['customer_id']);
                }

                if ($data['delete_avatar'] === true) {
                    $path = $customer->getFullImagePath();
                    if ($path) {
                        $customer->setImage(NULL)->setIsCustomImage(0)->save();
                        $data['image'] = null;
                        $data['is_custom_image'] = 0;
                        unlink($path);
                    }
                } elseif (!empty($data['avatar'])) {
                    $formated_name = md5($customer->getId());
                    $image_path = $customer->getBaseImagePath() . '/' . $formated_name;

                    // Create customer's folder
                    if (!is_dir($image_path)) {
                        mkdir($image_path, 0777, true);
                    }

                    // Store the picture on the server
                    $image_name = uniqid() . '.jpg';
                    $newavatar = base64_decode(str_replace(' ', '+', preg_replace('#^data:image/\w+;base64,#i', '', $data['avatar'])));
                    $file = fopen($image_path . "/" . $image_name, "wb");
                    fwrite($file, $newavatar);
                    fclose($file);

                    // Resize the image
                    Thumbnailer_CreateThumb::createThumbnail($image_path . '/' . $image_name, $image_path . '/' . $image_name, 256, 256, 'jpg', true);

                    $oldImage = $customer->getFullImagePath();

                    // Set the image to the customer
                    $customer->setImage('/' . $formated_name . '/' . $image_name)->setIsCustomImage(1)->save();
                    $data['image'] = '/' . $formated_name . '/' . $image_name;
                    $data['is_custom_image'] = 1;

                    if ($oldImage) {
                        unlink($oldImage);
                    }
                }

                $password = '';
                if (($data['change_password'] == true) && !empty($data['password'])) {

                    if (empty($data['old_password']) ||
                        (!empty($data['old_password']) &&
                            !$customer->isSamePassword($data['old_password']))) {
                        throw new Exception(__('The old password does not match the entered password.'));
                    }

                    $password = $data['password'];
                }

                $customer->setData($data);
                if (!empty($password)) {
                    $customer->setPassword($password);
                    Hook::trigger('mobile.customer.changePassword.success', [
                        'appId' => $this->getApplication()->getId(),
                        'customerId' => $customer->getId(),
                        'customer' => $customer,
                        'newPassword' => $password,
                        'token' => Zend_Session::getId(),
                        'type' => 'account'
                    ]);

                }
                if (!empty($data['metadatas'])) {
                    $customer->setMetadatas($data['metadatas']);
                }

                // New mobile account hooks/forms
                if (array_key_exists('extendedFields', $data)) {
                    Account::saveFields([
                        'application' => $this->getApplication(),
                        'request' => $this->getRequest(),
                        'session' => $this->getSession(),
                    ], $data['extendedFields']);
                }

                $customer->save();


                $currentCustomer = Customer_Model_Customer::getCurrent();

                $currentCustomer['extendedFields'] = Account::getFields([
                    'application' => $this->getApplication(),
                    'request' => $this->getRequest(),
                    'session' => $this->getSession(),
                ]);

                $html = [
                    'success' => true,
                    'message' => __('Info successfully saved'),
                    'clearCache' => $clearCache,
                    'customer' => $currentCustomer
                ];

            } catch (\Exception $e) {
                $html = ['error' => true, 'message' => $e->getMessage()];
            }

            $this->_sendJson($html);

        }

    }

}
