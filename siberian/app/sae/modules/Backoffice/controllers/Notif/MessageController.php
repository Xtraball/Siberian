<?php

class Backoffice_Notif_MessageController extends Backoffice_Controller_Default {

    public function loadAction() {

        $html = array(
            "title" => $this->_("Message"),
            "icon" => "fa-envelope-o",
        );

        $this->_sendHtml($html);

    }

    public function findAction() {

        try {
            $notif = new Backoffice_Model_Notification();
            $notif = $notif->find($this->getRequest()->getParam("message_id"));

            # Mark as read on opening
            $notif->setIsRead(true)->save();

            $link = $notif->getLink();
            if(strpos($link, "updates.siberiancms.com") !== false) {
                $link = str_replace("http://", "https://", $link);
            }

            $origin = '-';
            switch($notif->getObjectType()) {
                case "System_Model_SslCertificates":
                        $origin = "letsencrypt";
                    break;
                case "Application_Model_SourceQueue":
                case "Application_Model_ApkQueue":
                        $origin = "generator";
                    break;
                case "Android_Sdk_Update":
                    $origin = "android-sdk-update";
                    break;
            }

            $data = array(
                "success" => 1,
                "notif" => array(
                    "id"            => $notif->getId(),
                    "title"         => $notif->getTitle(),
                    "description"   => $notif->getDescription(),
                    "link"          => $link,
                    "priority"      => ($notif->getIsHighPriority()),
                    "source"        => $notif->getSource(),
                    "type"          => $notif->getType(),
                    "origin"        => $origin,
                    "object_type"   => $notif->getObjectType(),
                    "object_id"     => $notif->getObjectId(),
                    "created_at"    => $notif->getFormattedCreatedAt()
                ),
            );

        } catch (Exception $e) {
            $data = array(
                "error" => 1,
                "message" => $e->getMessage(),
            );
        }

        $this->_sendHtml($data);

    }

}
