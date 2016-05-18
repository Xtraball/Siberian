<?php

class Push_ApplicationController extends Application_Controller_Default
{

    public function editpostAction() {

        if($data = $this->getRequest()->getPost()) {

            $html = '';

            try {

                $message = new Push_Model_Message();
                $message->setMessageTypeByOptionValue($this->getCurrentOptionValue()->getOptionId());
                $sendNow = false;
                $inputs = array('send_at', 'send_until');

                foreach($inputs as $input) {
                    if(empty($data[$input.'_a_specific_datetime'])) {
                        $data[$input] = null;
                    }
                    else if(empty($data[$input])) {
                        throw new Exception($this->_('Please, enter a valid date'));
                    }
                    else {
                        $date = new Zend_Date($data[$input]);
                        $data[$input] = $date->toString('y-MM-dd HH:mm:ss');
                    }
                }

                if(empty($data['send_at'])) {
                    $sendNow = true;
                    $data['send_at'] = Zend_Date::now()->toString('y-MM-dd HH:mm:ss');
                }

                if(!empty($data['send_until']) AND $data['send_at'] > $data['send_until']) {
                    throw new Exception($this->_("The duration limit must be higher than the sent date"));
                }

                // Récupère l'option_value en cours
                $option_value = $this->getCurrentOptionValue();

                if(!empty($data['file'])) {

                    $file = pathinfo($data['file']);
                    $filename = $file['basename'];
                    $relative_path = $option_value->getImagePathTo();
                    $folder = Application_Model_Application::getBaseImagePath().$relative_path;
                    $img_dst = $folder.$filename;
                    $img_src = Core_Model_Directory::getTmpDirectory(true).'/'.$filename;

                    if(!is_dir($folder)) {
                        mkdir($folder, 0777, true);
                    }

                    if(!copy($img_src, $img_dst)) {
                        throw new exception($this->_('An error occurred while saving your picture. Please try again later.'));
                    } else {
                        $data['cover'] = $relative_path . $filename;
                    }
                }else if(!empty($data['remove_cover'])) {
                    $data['cover'] = null;
                }

                if(empty($data['action_value'])) {
                    $data['action_value'] = null;
                } else if(!preg_match('/^[0-9]*$/', $data['action_value'])) {
                    $url = "http://".$data['action_value'];
                    if(stripos($data['action_value'], "http://") !== false || stripos($data['action_value'], "https://") !== false) {
                        $url = $data['action_value'];
                    }

                    $data['action_value'] = file_get_contents("http://tinyurl.com/api-create.php?url=".urlencode($url));
                }

                $data['type_id'] = $message->getMessageType();
                $data['app_id'] = $this->getApplication()->getId();
                $data["send_to_all"] = $data["topic_receiver"]?0:1;
                $data["send_to_specific_customer"] = $data["customers_receiver"]?1:0;
                $message->setData($data)->save();

                //PnTopics
                if($data["topic_receiver"]) {
                    $topic_data = explode(";",$data["topic_receiver"]);

                    foreach($topic_data as $id_topic) {
                        if($id_topic != "") {
                            $category_message = new Topic_Model_Category_Message();
                            $category_message_data = array(
                                "category_id" => $id_topic,
                                "message_id" => $message->getId()
                            );
                            $category_message->setData($category_message_data);
                            $category_message->save();
                        }
                    }
                }

                //PUSH TO USER ONLY
                if(Push_Model_Message::hasTargetedNotificationsModule()) {
                    if ($data["customers_receiver"]) {
                        $customers_data = explode(";", $data["customers_receiver"]);

                        foreach ($customers_data as $id_customer) {
                            if ($id_customer != "") {
                                $customer_message = new Push_Model_Customer_Message();
                                $customer_message_data = array(
                                    "customer_id" => $id_customer,
                                    "message_id" => $message->getId()
                                );
                                $customer_message->setData($customer_message_data);
                                $customer_message->save();
                            }
                        }
                    }
                }

                if($message->getMessageType()==1) {
                    if ($sendNow) {
                        $c = curl_init();
                        curl_setopt($c, CURLOPT_URL, $this->getUrl('push/message/send', array('message_id' => $message->getId())));
                        curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);  // Follow the redirects (needed for mod_rewrite)
                        curl_setopt($c, CURLOPT_HEADER, false);         // Don't retrieve headers
                        curl_setopt($c, CURLOPT_NOBODY, true);          // Don't retrieve the body
                        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);  // Return from curl_exec rather than echoing
                        curl_setopt($c, CURLOPT_FRESH_CONNECT, true);   // Always ensure the connection is fresh

                        // Timeout super fast once connected, so it goes into async.
                        curl_setopt($c, CURLOPT_TIMEOUT, 10);
                        curl_exec($c);
                        curl_close($c);

                    }
                } else {
                    $message->updateStatus('delivered');
                }

                $html = array(
                    'success' => 1,
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                );

                if($sendNow) $html['success_message'] = $this->_('Your message has been saved successfully and will be sent in a few minutes');
                else $html['success_message'] = $this->_('Your message has been saved successfully and will be sent at the entered date');

            }
            catch(Exception $e) {
                $html = array(
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));

        }

    }

    public function cropAction() {

        if($datas = $this->getRequest()->getPost()) {
            try {
                $uploader = new Core_Model_Lib_Uploader();
                $file = $uploader->savecrop($datas);
                $datas = array(
                    'success' => 1,
                    'file' => $file,
                    'message_success' => $this->_('Info successfully saved'),
                    'message_button' => 0,
                    'message_timeout' => 2,
                );
            } catch (Exception $e) {
                $datas = array(
                    'error' => 1,
                    'message' => $e->getMessage()
                );
            }
            $this->getLayout()->setHtml(Zend_Json::encode($datas));
        }

    }

}