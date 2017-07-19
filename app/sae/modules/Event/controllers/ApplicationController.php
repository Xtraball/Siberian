<?php

class Event_ApplicationController extends Application_Controller_Default {

    /**
     * @var array
     */
    public $cache_triggers = array(
        "editpost" => array(
            "tags" => array(
                "feature_paths_valueid_#VALUE_ID#",
                "assets_paths_valueid_#VALUE_ID#",
            ),
        ),
        "delete" => array(
            "tags" => array(
                "feature_paths_valueid_#VALUE_ID#",
                "assets_paths_valueid_#VALUE_ID#",
            ),
        ),
    );

    public function formAction() {

        $option_value = new Application_Model_Option_Value();

        if($this->getRequest()->getParam("option_value_id")) {
            $value_id = $this->getRequest()->getParam("option_value_id");
            $name = $this->getRequest()->getParam("name");
            $event_type = strtolower($this->getRequest()->getParam("event_type"));
            $option_value->find($value_id);
        }

        try {
            $event = new Event_Model_Event();
            if($id = $this->getRequest()->getParam("id")) {
                $event->find($id);
            }

            switch($event_type) {
                case "ical":
                    $template = "event/application/edit/ical/form.phtml";
                    $block    = "admin_view_default";
                    break;

                case "fb":
                    $template = "event/application/edit/facebook/form.phtml";
                    $block    = "admin_view_default";
                    break;

                case "cstm":
                    $template    = "event/application/edit/custom/form.phtml";
                    $block       = "event_view_application_edit_custom_form";
                    break;
            }

            $this->getLayout()->setBaseRender("form", $template, $block)
                ->setCurrentEvent($event)
                ->setOptionValue($option_value)
                ->setName($name)
                ->setEventType($event_type);

            $html = array(
                "form_html" => $this->getLayout()->render(),
                "event_type" => $event_type,
                "success" => 1
            );

        } catch (Exception $e) {
            $html = array(
                "message" => $e->getMessage()
            );
        }
        $this->getLayout()->setHtml(Zend_Json::encode($html));

    }

    public function editpostAction() {

        if($datas = $this->getRequest()->getPost()) {

            try {

                $option_value = $this->getCurrentOptionValue();
                $event = new Event_Model_Event();
                $data = array();

                if(!empty($datas["id"])) {
                    $event->find($datas["id"]);
                    if($event->getValueId() != $option_value->getId()) {
                        throw new Siberian_Exception(__("An error occurred while saving. Please try again later."));
                    }
                }

                if(!$event->getId()) {
                    $event->setValueId($option_value->getId());
                }

                if($datas["event_type"] == "ical"){
                    if(empty($datas["url"]) OR !Zend_Uri::check($datas["url"])) {
                        throw new Siberian_Exception(__("Please enter a valid url"));
                    }
                }

                $data["name"]       = $datas["name"];
                $data["event_type"] = $datas["event_type"];
                $data["url"]        = isset($datas["url"]) ? $datas["url"] : null;

                $event->addData($data)->save();

                $cache = Zend_Registry::get("cache");
                $cache->remove($event->getCacheId());

                /** Update touch date, then never expires (until next touch) */
                $option_value
                    ->touch()
                    ->expires(-1);

                $html = array(
                    "success" => true,
                    "agenda_id" => $event->getId(),
                    "success_message" => __("Event successfully saved"),
                    "message_timeout" => 2,
                    "message_button" => 0,
                    "message_loader" => 0
                );

            }
            catch(Exception $e) {
                $html = array(
                    "message" => $e->getMessage(),
                    "message_button" => 1,
                    "message_loader" => 1
                );
            }

            $this->_sendJson($html);

        }

    }

    public function checkfbAction() {

        if($datas = $this->getRequest()->getPost()) {
            try {

                $username = "";

                if(empty($datas["url"])) throw new Exception();
                if(stripos($datas["url"], "facebook.com") !== false AND stripos($datas["url"], "http") === false) {
                    $datas["url"] = "https://".$datas["url"];
                }
                if(Zend_Uri::check($datas["url"])) {
                    $uri = Zend_Uri_Http::fromString($datas["url"]);
                    $username = ltrim($uri->getPath(), "/");
                    $username = current(explode("/", $username));
                }
                else {
                    $username = $datas["url"];
                }

                $access_token = Core_Model_Lib_Facebook::getAppToken();

                $url = "https://graph.facebook.com/v2.7/$username/events?access_token=$access_token";
                $response = file_get_contents($url);

                if(empty($response)) throw new Exception("Invalid username");

                $html = array(
                    "success" => 1,
                    "fb_username" => $username
                );

            }
            catch(Exception $e) {
                $html = array(
                    "message" => $e->getMessage(),
                    "message_button" => 1,
                    "message_loader" => 1
                );
            }

            $this->_sendJson($html);

        }

    }

    public function deleteAction() {
        $id = $this->getRequest()->getParam("id");
        $html = "";
        try {

            $event = new Event_Model_Event();
            $event->find($id)->delete();

            $html = array(
                "event_id" => $id,
                "success" => 1,
                "success_message" => __("Calendar successfully deleted"),
                "message_timeout" => 2,
                "message_button" => 0,
                "message_loader" => 0
            );

            $cache = Zend_Registry::get("cache");
            $cache->remove($event->getCacheId());
        } catch (Exception $e) {
            $html = array(
                "message" => $e->getMessage(),
                "url" => "/event/admin/list"
            );
        }

        $this->_sendJson($html);
    }

}