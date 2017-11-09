<?php

class Topic_Mobile_ListController extends Application_Controller_Mobile_Default {

    public function findallAction() {
        $html = array("collection" => array());
        try {
            if($data = $this->getRequest()->getParams()) {
//                if($device_uid = $this->getRequest()->getParam('device_uid')) {
                    $device_uid = $this->getRequest()->getParam('device_uid');
                    $topic = new Topic_Model_Topic();
                    $topic->find(array("value_id" => $data["value_id"]));

                    $subscription = new Topic_Model_Subscription();

                    $html = array();
                    if ($topic->getId()) {

                        $html["description"] = $topic->getDescription();

                        $parent_categories = $topic->getCategories();
                        foreach ($parent_categories as $category) {
                            $picture = $category->getPicture() ? $this->getRequest()->getBaseUrl() . Application_Model_Application::getImagePath() . $category->getPicture() : "";

                            $data_category = array(
                                "id" => $category->getId(),
                                "name" => $category->getName(),
                                "description" => $category->getDescription(),
                                "picture" => $picture,
                                "is_subscribed" => $device_uid ? $subscription->isSubscribed($category->getId(), $device_uid) : null
                            );

                            $children = $category->getChildren();
                            $data_children = array();
                            foreach ($children as $child) {

                                $picture = $child->getPicture() ? $this->getRequest()->getBaseUrl() . Application_Model_Application::getImagePath() . $child->getPicture() : "";

                                $data_children[] = array(
                                    "id" => $child->getId(),
                                    "name" => $child->getName(),
                                    "description" => $child->getDescription(),
                                    "picture" => $picture,
                                    "is_subscribed" => $device_uid ? $subscription->isSubscribed($child->getId(), $device_uid) : null
                                );
                            }
                            $data_category["children"] = $data_children;
                            $html["collection"][] = $data_category;
                        }
                    }
//                }
            }
        }
        catch(Exception $e) {
            $html = array(
                'error' => 1,
                'message' => $e->getMessage(),
                'message_button' => 1,
                'message_loader' => 1
            );
        }
        $html["page_title"] = $this->getCurrentOptionValue()->getTabbarName();
        $this->_sendHtml($html);
    }

    public function subscribeAction() {
        try {
            if($data = Zend_Json::decode($this->getRequest()->getRawBody())) {
                if($data["device_uid"] AND $data["category_id"]) {
                    $subscription = new Topic_Model_Subscription();
                    if($data["subscribe"]===true) {
                        $subscription->setData($data)->save();
                    } else {
                        $subscriptions = $subscription->findAll(array("device_uid" => $data["device_uid"], "category_id" => $data["category_id"]));
                        foreach($subscriptions as $subscription) {
                            if ($subscription->getId()) {
                                $subscription->delete();
                            }
                        }
                    }

                    $html = array(
                        'success' => 1
                    );

                } else {
                    throw new exception($this->_('An error occurred while saving. Please try again later.'));
                }
            }
        }
        catch(Exception $e) {
            $html = array(
                'error' => 1,
                'message' => $e->getMessage(),
                'message_button' => 1,
                'message_loader' => 1
            );
        }

        $this->_sendHtml($html);
    }
}
