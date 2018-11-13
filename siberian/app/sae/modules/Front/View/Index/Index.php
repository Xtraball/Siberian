<?php

/**
 * Class Front_View_Index_Index
 */
class Front_View_Index_Index extends Front_View_Default
{

    /**
     * @return mixed|string
     */
    public function getFavicon()
    {

        try {

            $favicon = "";
            if ($this->getCurrentWhiteLabelEditor()) {
                $favicon = $this->getCurrentWhiteLabelEditor()->getFaviconUrl();
            }

            if (!$favicon) {
                $favicon = System_Model_Config::getValueFor("favicon");
            }

            if (!$favicon) {
                $favicon = "/favicon.png";
            }

        } catch (Exception $e) {
            $favicon = "/favicon.png";
        }

        return $favicon;
    }

    /**
     * @return bool|mixed|string
     * @throws Zend_Exception
     */
    public function getLogo()
    {

        try {

            $logo = "";
            if ($this->getCurrentWhiteLabelEditor()) {
                $logo = $this->getCurrentWhiteLabelEditor()->getLogoUrl();
            }

            if (!$logo) {
                $logo = __get("logo");
            }

            if (!$logo) {
                $logo = $this->getImage("header/logo.png");
            }

        } catch (Exception $e) {
            $logo = $this->getImage("header/logo.png");
        }

        return $logo;

    }

    /**
     * @return bool
     */
    protected function _canAccessWhiteLabelEditor()
    {
        return Installer_Model_Installer::hasModule("Whitelabel") &&
            !$this->getCurrentWhiteLabelEditor() && $this->_canAccess("white_label_editor");
    }

}