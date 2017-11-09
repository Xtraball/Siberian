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
     * @param $sTitle
     * @return Siberian_Service_Push_Gcm_Message
     */
    public function setImage($sImage) {
        $this->addData("image", $sImage);

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
     * @param $iValueID
     * @return Siberian_Service_Push_Gcm_Message
     */
    public function setValueId($iValueID) {
        $this->addData("value_id", $iValueID);

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
    public function setCover($sCover, $sPicture, $sSummaryText) {
        if(!empty($sCover)) {
            $this->addData("cover", $sCover);

            $this->addData("style", "picture");
            $this->addData("picture", $sPicture);
            $this->addData("summaryText", $sSummaryText);
        }

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
