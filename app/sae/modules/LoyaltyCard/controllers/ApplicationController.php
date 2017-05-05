<?php

class LoyaltyCard_ApplicationController extends Application_Controller_Default
{

    public function editpostAction() {

        $values = $this->getRequest()->getPost();

        $form = new LoyaltyCard_Form_Create();
        if($form->isValid($values)) {
            $loyalty_card = new LoyaltyCard_Model_LoyaltyCard();
            $loyalty_card->setData($form->getValues());

            if($values["image_active"] == "_delete_") {
                $loyalty_card->setData("image_active", "");
            } else if(file_exists(Core_Model_Directory::getBasePathTo("images/application".$values["image_active"]))) {
                # Nothing changed, skip
            } else {
                $path_image_active = Siberian_Feature::moveUploadedFile($this->getCurrentOptionValue(), Core_Model_Directory::getTmpDirectory()."/".$values['image_active'], $values['image_active']);
                $loyalty_card->setData("image_active", $path_image_active);
            }

            if($values["image_inactive"] == "_delete_") {
                $loyalty_card->setData("image_inactive", "");
            } else if(file_exists(Core_Model_Directory::getBasePathTo("images/application".$values["image_inactive"]))) {
                # Nothing changed, skip
            } else {
                $path_image_inactive = Siberian_Feature::moveUploadedFile($this->getCurrentOptionValue(), Core_Model_Directory::getTmpDirectory()."/".$values['image_inactive'], $values['image_inactive']);
                $loyalty_card->setData("image_inactive", $path_image_inactive);
            }

            $loyalty_card->save();

            $html = array(
                "success" => 1,
                "message" => __("Your loyalty card has been saved successfully"),
            );

        } else {
            $html = array(
                "error" => 1,
                "message" => $form->getTextErrors(),
                "errors" => $form->getTextErrors(true)
            );
        }

        $this->_sendHtml($html);

    }

    public function savepasswordAction()
    {

        if ($datas = $this->getRequest()->getPost()) {

            $html = '';

            try {

                $isNew = true;

                $password = new LoyaltyCard_Model_Password();
                $application = $this->getApplication();
                $password_id = $datas['password_id'];

                if (!empty($datas['password_id'])) {
                    $password->find($datas['password_id']);
                    if ($password->getValueId() != $datas['value_id']) {
                        throw new Exception(__("An error occurred while saving the password. Please try again later"));
                    }
                    $isNew = false;
                } else {
                    $datas['app_id'] = $application->getId();
                }

                if (empty($datas['is_deleted'])) {
                    if (empty($datas['name'])) throw new Exception(__('Please enter a name'));

                    if (empty($datas['password'])/* OR empty($datas['confirm_password'])*/) {
                        throw new Exception(__('Please enter a password'));
                    }
                    if (strlen($datas['password']) < 4 OR !ctype_digit($datas['password'])/* OR empty($datas['confirm_password'])*/) {
                        throw new Exception(__('Your password must be 4 digits'));
                    }

                    $password->setPassword(sha1($datas['password']));
                    if ($datas['password']) unset($datas['password']);
                } else if (!$password->getId()) {
                    throw new Exception(__('An error occurred while saving the password. Please try again later.'));
                }
                $password->addData($datas)
                    ->save();

                $html = array('success' => 1, 'id' => $password->getId());
                if (!empty($datas['is_deleted'])) {
                    $html['is_deleted'] = 1;
                    $html['id'] = $password_id;
                } else if ($isNew) {
                    $html['is_new'] = 1;
                    $html['name'] = $password->getName();
                }

            } catch (Exception $e) {
                $html = array('message' => $e->getMessage());
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));

        }
    }

    public function dlqrcodeAction()
    {

        if ($data = $this->getRequest()->getParams()) {

            $html = '';

            try {

                $password = new LoyaltyCard_Model_Password();
                $application = $this->getApplication();
                $password_id = $data['password_id'];

                $password->find($data['password_id']);
                if(!$password->getId()) {
                    throw new Exception(__("An error occurred while retrieving QRCode. Please try again later"));
                }

                if(!$password->getUnlockCode()) {
                    $unlock_code = uniqid();
                    $data["unlock_code"] = $unlock_code;
                    $password->addData($data)
                        ->save();
                } else {
                    $unlock_code = $password->getUnlockCode();
                }

                $dir_image = Core_Model_Directory::getBasePathTo("/images/application/".$this->getApplication()->getId());

                if(!is_dir($dir_image)) mkdir($dir_image, 0775, true);
                if(!is_dir($dir_image."/application")) mkdir($dir_image."/application", 0775, true);
                if(!is_dir($dir_image."/application/qrloyalty")) mkdir($dir_image."/application/qrloyalty", 0775, true);

                $dir_image .= "/application/qrloyalty/";
                $image_name = $password->getId()."-qrloyalty.png";

                if(!is_file($dir_image.$image_name)) {
                    copy('https://api.qrserver.com/v1/create-qr-code/?color=000000&bgcolor=FFFFFF&data=sendback%3A'.$unlock_code.'&qzone=1&margin=0&size=200x200&ecc=L', $dir_image.$image_name);
                }

                $img = imagecreatefrompng($dir_image.$image_name);
                $readable_name = $password->getName();
                header('Content-Type: image/png');
                header('Content-Disposition: attachment; filename="'.$readable_name.'.png"');
                imagepng($img);
                imagedestroy($img);
                die();

            } catch (Exception $e) {
                $html = $e->getMessage();
            }

            echo $html; die();

        }
    }

}