<?php

namespace Siberian\Service\Push\CloudMessaging;

use Siberian\CloudMessaging\Message as BaseMessage;

/**
 * Class Message
 * @package Siberian\Service\Push\CloudMessaging
 */
class Message extends BaseMessage
{
    /**
     * @param $iMessageId
     * @return $this
     */
    public function setMessageId($iMessageId)
    {
        $this->addData("message_id", $iMessageId);

        return $this;
    }

    /**
     * @param $sTitle
     * @return $this
     */
    public function setTitle($sTitle)
    {
        $this->addData("title", $sTitle);

        return $this;
    }

    /**
     * @param $sImage
     * @return $this
     */
    public function setImage($sImage)
    {
        $this->addData("image", $sImage);

        return $this;
    }

    /**
     * @param $sMessage
     * @return $this
     */
    public function setMessage($sMessage)
    {
        $this->addData("message", $sMessage);

        return $this;
    }

    /**
     * @param $iValueID
     * @return $this
     */
    public function setValueId($iValueID)
    {
        $this->addData("value_id", $iValueID);

        return $this;
    }

    /**
     * @param $iTimeToLive
     * @return $this
     */
    public function setTimeToLive($iTimeToLive)
    {
        $this->timeToLive($iTimeToLive);

        return $this;
    }

    /**
     * @param bool $bDelayWithIdle
     * @return $this
     */
    public function setDelayWithIdle($bDelayWithIdle = false)
    {
        $this->delayWhileIdle($bDelayWithIdle);

        return $this;
    }

    /**
     * @param $sLatitude
     * @param $sLongitude
     * @param $sRadius
     * @return $this
     */
    public function setGeolocation($sLatitude, $sLongitude, $sRadius)
    {
        $this
            ->addData("latitude", $sLatitude)
            ->addData("longitude", $sLongitude)
            ->addData("radius", $sRadius);

        return $this;
    }

    /**
     * @param int $dSendUntil
     * @return $this
     */
    public function setSendUntil($dSendUntil = 0)
    {
        $this->addData("send_until", $dSendUntil);

        return $this;
    }

    /**
     * @param $sCover
     * @param $sPicture
     * @param $sSummaryText
     * @return $this
     */
    public function setCover($sCover, $sPicture, $sSummaryText)
    {
        if (!empty($sCover)) {
            $this->addData("cover", $sCover);

            $this->addData("style", "picture");
            $this->addData("picture", $sPicture);
            $this->addData("summaryText", $sSummaryText);
        }

        return $this;
    }

    /**
     * @param $sActionValue
     * @return $this
     */
    public function setActionValue($sActionValue)
    {
        $this->addData("action_value", $sActionValue);

        return $this;
    }

    /**
     * @param $bOpenWebview
     * @return $this
     */
    public function setOpenWebview($bOpenWebview)
    {
        $this->addData("open_webview", $bOpenWebview);

        return $this;
    }

}
