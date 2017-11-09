<?php

/**
 * Class Siberian_Service_Push_Apns_Message
 *
 * Custom APNS Message, with specific values from SiberianCMS
 */
class Siberian_Service_Push_Apns_Message extends ApnsPHP_Message_Custom {

    /**
     * @param $iMessageId
     */
    public function setMessageId($iMessageId) {
        $this->_iMessageId = $iMessageId;
    }

    /**
     * @param $sLatitude
     * @param $sLongitude
     * @param $sRadius
     */
    public function setGeolocation($sLatitude, $sLongitude, $sRadius)
    {
        $this->_sLatitude = $sLatitude;
        $this->_sLongitude = $sLongitude;
        $this->_sRadius = $sRadius;
    }

    /**
     * @param $sSendUntil
     */
    public function setSendUntil($sSendUntil)
    {
        $this->_sSendUntil = $sSendUntil;
    }

    /**
     * @param $sUserInfo
     */
    public function setUserInfo($sUserInfo)
    {
        $this->_sUserInfo = $sUserInfo;
    }

    /**
     * @param $sOpenWebView
     */
    public function setOpenWebView($sOpenWebView)
    {
        $this->_sOpenWebView = $sOpenWebView;
    }

    /**
     * @param $sCover
     */
    public function setCover($sCover)
    {
        $this->_sCover = $sCover;
    }

    /**
     * @param $sActionValue
     */
    public function setActionValue($sActionValue)
    {
        $this->_sActionValue = $sActionValue;
    }

    /**
     * @param $iValueId
     */
    public function setValueId($iValueID)
    {
        $this->_iValueId = $iValueID;
    }

    /**
     * Get the payload dictionary.
     *
     * @return @type array The payload dictionary.
     */
    public function _getPayload()
    {
        $aPayload = parent::_getPayload();

        if (isset($this->_iValueId)) {
            $aPayload['aps']['value_id'] = (int)$this->_iValueId;
        }

        if (isset($this->_sLatitude) && isset($this->_sLongitude) && isset($this->_sRadius)) {
            $aPayload['aps']['latitude'] = (string)$this->_sLatitude;
            $aPayload['aps']['longitude'] = (string)$this->_sLongitude;
            $aPayload['aps']['radius'] = (string)$this->_sRadius;
        }

        if(isset($this->_iMessageId)) {
            $aPayload['aps']['message_id'] = $this->_iMessageId;
        }

        if(isset($this->_sSendUntil)) {
            $aPayload['aps']['send_until'] = $this->_sSendUntil;
        }

        if(isset($this->_sSendUntil) && (is_null($this->_sSendUntil) || empty($this->_sSendUntil))) {
            $aPayload['aps']['send_until'] = null;
        }

        if(isset($this->_sUserInfo)) {
            $aPayload['aps']['user_info'] = $this->_sUserInfo;
        }

        if(isset($this->_sOpenWebView)) {
            $aPayload['aps']['alert']['open_webview'] = $this->_sOpenWebView;
        }

        if(isset($this->_sCover)) {
            $aPayload['aps']['alert']['cover'] = $this->_sCover;
        }

        if(isset($this->_sActionValue)) {
            $aPayload['aps']['alert']['action_value'] = $this->_sActionValue;
        }

        return $aPayload;
    }
}
