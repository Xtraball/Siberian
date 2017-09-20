<?php

class Push_ApplicationController extends Application_Controller_Default
{

    /**
     * @var array
     */
    public $cache_triggers = array(
        "editpost" => array(
            "tags" => array(
                "feature_paths_valueid_#VALUE_ID#"
            ),
        )
    );

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
                        throw new Exception(__('Please, enter a valid date'));
                    }
                    else {
                        $date = new Zend_Date($data[$input], 'y-MM-dd HH:mm:ss');
                        $data[$input] = $date->toString('y-MM-dd HH:mm:ss');
                    }
                }

                //Inapp message expiration date
                if(!empty($data["inapp_datepicker_send_until"])) {
                    $date = new Zend_Date($data["inapp_datepicker_send_until"],
                        'y-MM-dd HH:mm:ss');
                    $date_now = new Zend_Date();
                    $date_now = $date_now->toString('y-MM-dd HH:mm:ss');
                    $data["send_until"] = $date->toString('y-MM-dd HH:mm:ss');
                    if($data["send_until"] < $date_now) {
                        throw new Exception(__("The duration limit must be higher than the sent date"));
                    }
                }

                if(empty($data['send_at'])) {
                    $data['send_at'] = null;
                }

                if(!empty($data['send_until']) AND $data['send_at'] > $data['send_until']) {
                    throw new Exception(__("The duration limit must be higher than the sent date"));
                }



                // Récupère l'option_value en cours
                $option_value = $this->getCurrentOptionValue();

                if (!empty($data['file'])) {

                    $file = pathinfo($data['file']);
                    $filename = $file['basename'];
                    $relative_path = $option_value->getImagePathTo();
                    $folder = Application_Model_Application::getBaseImagePath() . $relative_path;
                    $img_dst = $folder . $filename;
                    $img_src = Core_Model_Directory::getTmpDirectory(true) . '/' . $filename;

                    if(!is_dir($folder)) {
                        mkdir($folder, 0777, true);
                    }

                    if(!copy($img_src, $img_dst)) {
                        throw new exception(__('An error occurred while saving your picture. Please try again later.'));
                    } else {
                        $data['cover'] = $relative_path . $filename;
                    }
                } else if(!empty($data['remove_cover'])) {
                    $data['cover'] = null;
                }

                if (empty($data['action_value'])) {
                    $data['action_value'] = null;
                } else if(!preg_match('/^[0-9]*$/', $data['action_value'])) {
                    $url = "http://".$data['action_value'];
                    if(stripos($data['action_value'], "http://") !== false ||
                        stripos($data['action_value'], "https://") !== false) {
                        $url = $data['action_value'];
                    }

                    $data['action_value'] = file_get_contents("https://tinyurl.com/api-create.php?url=".urlencode($url));
                }

                $data['type_id'] = $message->getMessageType();
                $data['app_id'] = $this->getApplication()->getId();
                $data["send_to_all"] = $data["topic_receiver"]?0:1;
                $data["send_to_specific_customer"] = $data["customers_receiver"]?1:0;
                $data["base_url"] = $this->getRequest()->getBaseUrl();

                if(empty($data["customers_receiver"]) && empty($data["customers_receiver"])) {
                    $data["target_devices"] = $data["devices"];
                } else {
                    $data["target_devices"] = "all";
                }


                $message->setData($data);
                // Use new methods for automatic base64 conversion
                $message->setTitle($data["title"])->setText($data["text"]);
                $message->save();

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
                if(Push_Model_Message::hasIndividualPush()) {
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

                if($message->getMessageType() != Push_Model_Message::TYPE_PUSH) {
                    $message->updateStatus('delivered');
                }

                /** Fallback for SAE, or disabled cron */
                if(!Cron_Model_Cron::is_active()) {
                    $cron = new Cron_Model_Cron();
                    $task = $cron->find("pushinstant", "command");
                    Siberian_Cache::__clearLocks();
                    $siberian_cron = new Siberian_Cron();
                    $siberian_cron->execute($task);
                }

                /** Update touch date, then never expires (until next touch) */
                $this->getCurrentOptionValue()
                    ->touch()
                    ->expires(-1);

                $html = array(
                    'success' => 1,
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                );

                if($sendNow) $html['success_message'] = __('Your message has been saved successfully and will be sent in a few minutes');
                else $html['success_message'] = __('Your message has been saved successfully and will be sent at the entered date');

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
                    'message_success' => __('Info successfully saved'),
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

    /**
     * @param $option
     * @return string
     * @throws Exception
     */
    public function exportAction() {
        if($this->getCurrentOptionValue()) {
            $push = new Push_Model_Message();
            $result = $push->exportAction($this->getCurrentOptionValue());

            $this->_download($result, "push-".date("Y-m-d_h-i-s").".yml", "text/x-yaml");
        }
    }

}
