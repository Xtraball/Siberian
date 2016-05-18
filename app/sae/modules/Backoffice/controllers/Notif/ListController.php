<?php

class Backoffice_Notif_ListController extends Backoffice_Controller_Default {

    public function loadAction() {

        $html = array(
            "title" => $this->_("Messages"),
            "icon" => "fa-envelope",
        );

        $this->_sendHtml($html);

    }

    public function findallAction() {

        $notif = new Backoffice_Model_Notification();
        $notif->update();
        $notifs = $notif->findAll();
        $data = array("notifs" => array());

        foreach($notifs as $notif) {
            $notif->setIsRead((bool) $notif->getIsRead());
            $notif->setIsHighPriority((bool) $notif->getIsHighPriority());
            $data["notifs"][] = $notif->getData();
        }

        $this->_sendHtml($data);

    }

    public function markasAction() {

        if($data = Zend_Json::decode($this->getRequest()->getRawBody())) {

            try {

                if(empty($data["notif_id"])) {
                    throw new Exception($this->_('An error occurred while saving. Please try again later.'));
                }

                $notification = new Backoffice_Model_Notification();
                $notification->find($data["notif_id"]);

                if(!$notification->getId()) {
                    throw new Exception($this->_('An error occurred while saving. Please try again later.'));
                }

                $is_read = (int) !empty($data["is_read"]);
                $notification->setIsRead($is_read)->save();

                $html = array(
                    'success' => 1,
                    'is_read' => $is_read
                );

            } catch (Exception $e) {
                $html = array(
                    'error' => 1,
                    'message' => $e->getMessage()
                );
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));

        }

    }

}
