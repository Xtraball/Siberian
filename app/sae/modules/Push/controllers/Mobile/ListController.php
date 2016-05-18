<?php

class Push_Mobile_ListController extends Application_Controller_Mobile_Default {

    public function findallAction() {

        $data = array("collection" => array());
        $option = $this->getCurrentOptionValue();
        $color = $this->getApplication()->getBlock('background')->getColor();
        $offset = $this->getRequest()->getParam('offset',0);

        if($device_uid = $this->getRequest()->getParam('device_uid')) {
            $option_value = $this->getCurrentOptionValue();

            $message = new Push_Model_Message();
            $message->setMessageTypeByOptionValue($option_value->getOptionId());
            $messages = $message->findByDeviceId($device_uid, $option_value->getAppId(), $offset);

            $icon_new = $this->getRequest()->getBaseUrl() . ($option->getImage()->getCanBeColorized() ? $this->_getColorizedImage($option->getIconId(), $color) : $option->getIconUrl());
            $icon_pencil = $this->getRequest()->getBaseUrl() . $this->_getColorizedImage($this->_getImage("pictos/pencil.png"), $color);

            foreach($messages as $message) {

                $meta = array(
                    "date" => array(
                        "picto" => $icon_pencil,
                        "text" => $message->getFormattedCreatedAt(Zend_Date::DATETIME_MEDIUM)
                    )
                );

                if(!$message->getIsRead()) {
                    $meta["likes"] = array(
                        "picto" => $icon_new,
                        "text" => $this->_("New")
                    );
                }

                $picture = $message->getCover() ? $this->getRequest()->getBaseUrl() . Application_Model_Application::getImagePath() . $message->getCover() : null;

                $action_value = null;
                $url = null;
                if($message->getActionValue()) {
                    if(is_numeric($message->getActionValue())) {
                        $option_value = new Application_Model_Option_Value();
                        $option_value->find($message->getActionValue());
                        $action_value = $option_value->getPath(null, array('value_id' => $option_value->getId()), false);
                    } else {
                        $action_value = $message->getActionValue();
                        $url = $action_value;
                    }
                }

                if($this->getApplication()->getIcon(74)) {
                    $icon = $this->getRequest()->getBaseUrl() . $this->getApplication()->getIcon(74);
                } else {
                    $icon = null;
                }

                $data["collection"][] = array(
                    "id" => $message->getId(),
                    "author" => $message->getTitle(),
                    "message" => $message->getText(),
                    "topic" => $message->getLabel(),
                    "details" => $meta,
                    "picture" => $picture,
                    "icon" => $icon,
                    "action_value" => $action_value,
                    "url" => $url
                );
            }

            $message->markAsRead($device_uid);

        }

        $data["page_title"] = $this->getCurrentOptionValue()->getTabbarName();
        $data["displayed_per_page"] = Push_Model_Message::DISPLAYED_PER_PAGE;

        $this->_sendHtml($data);

    }

    public function countAction() {

        $nbr = 0;
        if($device_uid = $this->getRequest()->getParam('device_uid')) {
            $message = new Push_Model_Message();
            $message->setMessageTypeByOptionValue($this->getCurrentOptionValue()->getOptionId());
            $nbr = $message->countByDeviceId($device_uid);
        }

        $data = array('count' => $nbr);
        $this->_sendHtml($data);

    }

    protected function _getDeviceUid() {

        $id = null;
        if($device_uid = $this->getRequest()->getParam('device_uid')) {
            if(!empty($device_uid)) {
                if(strlen($device_uid) == 36) {
                    $device = new Push_Model_Iphone_Device();
                    $device->find($device_uid, 'device_uid');
                    $id = $device->getDeviceUid();
                }
                else {
                    $device = new Push_Model_Android_Device();
                    $device->find($device_uid, 'registration_id');
                    $id = $device->getRegistrationId();
                }
            }

        }

        return $id;

    }

}