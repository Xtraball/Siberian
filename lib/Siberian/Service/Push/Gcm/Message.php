<?php

use PHP_GCM\Message as Message;

/**
 * Class Siberian_Service_Push_Gcm_Message
 *
 * Custom GCM Message, with specific values from SiberianCMS
 */
class Siberian_Service_Push_Gcm_Message extends Message {

    /**
     * @param $iMessageId
     * @return Siberian_Service_Push_Gcm_Message
     */
    public function setMessageId($iMessageId) {
        $this->addData("message_id", $iMessageId);

        return $this;
    }

    /**
     * @param $sTitle
     * @return Siberian_Service_Push_Gcm_Message
     */
    public function setTitle($sTitle) {
        $this->addData("title", $sTitle);

        return $this;
    }

    /**
     * @param $sMessage
     * @return Siberian_Service_Push_Gcm_Message
     */
    public function setMessage($sMessage) {
        $this->addData("message", $sMessage);

        return $this;
    }

    /**
     * @param $iTimeToLive
     * @return Siberian_Service_Push_Gcm_Message
     */
    public function setTimeToLive($iTimeToLive) {
        $this->timeToLive($iTimeToLive);

        return $this;
    }

    /**
     * @param bool $bDelayWithIdle
     * @return Siberian_Service_Push_Gcm_Message
     */
    public function setDelayWithIdle($bDelayWithIdle = false) {
        $this->delayWhileIdle($bDelayWithIdle);

        return $this;
    }

    public function setGeolocation($sLatitude, $sLongitude, $sRadius) {
        $this
            ->addData("latitude", $sLatitude)
            ->addData("longitude", $sLongitude)
            ->addData("radius", $sRadius)
        ;

        return $this;
    }

    /**
     * @param int $dSendUntil
     * @return Siberian_Service_Push_Gcm_Message
     */
    public function setSendUntil($dSendUntil = 0) {
        $this->addData("send_until", $dSendUntil);

        return $this;
    }


    /**
     * @param $sCover
     * @return Siberian_Service_Push_Gcm_Message
     */
    public function setCover($sCover) {
        $this->addData("cover", $sCover);

        return $this;
    }

    /**
     * @param $sActionValue
     * @return Siberian_Service_Push_Gcm_Message
     */
    public function setActionValue($sActionValue) {
        $this->addData("action_value", $sActionValue);

        return $this;
    }

    /**
     * @param $bOpenWebview
     * @return Siberian_Service_Push_Gcm_Message
     */
    public function setOpenWebview($bOpenWebview) {
        $this->addData("open_webview", $bOpenWebview);

        return $this;
    }

}