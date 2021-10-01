<?php

use Siberian\Version;

/**
 * Class Application_View_Customization_Features_List_Options
 */
class Application_View_Customization_Features_List_Options extends Core_View_Default
{
    /**
     * @var string
     */
    protected $_icon_color;

    /**
     * @param $option
     * @param null $enforcedColor
     * @param bool $forceColorizable
     * @return string
     * @throws Zend_Exception
     * @throws Zend_Validate_Exception
     */
    protected function getIconUrl($option, $enforcedColor = null, $forceColorizable = false)
    {
        // Enforces a color (but not the colorization*)
        if ($enforcedColor !== null && empty($this->_icon_color)) {
            $this->_icon_color = $enforcedColor;
        }

        if ($option->getOptionId() === 'customer_account' &&
            $this->getApplication()->getAccountIconId()) {

            $image = (new Media_Model_Library_Image())
                ->find($this->getApplication()->getAccountIconId());
            $iconUrl = $image->getUrl();
            $colorizable = $image->getCanBeColorized();
        } else if ($option->getOptionId() === 'more_items' &&
            $this->getApplication()->getMoreIconId()) {

            $image = (new Media_Model_Library_Image())
                ->find($this->getApplication()->getMoreIconId());
            $iconUrl = $image->getUrl();
            $colorizable = $image->getCanBeColorized();
        } else {
            $image = (new Media_Model_Library_Image())
                ->find($option->getIconId());
            $iconUrl = $image->getUrl();
            $colorizable = $image->getCanBeColorized();
        }

        if ($colorizable || $forceColorizable) {
            if (!$this->_icon_color) {
                $this->_initIconColor();
            }

            $iconUrl = $this->getColorizedImage($iconUrl, $this->_icon_color);
        }

        return $iconUrl;
    }

    /**
     * @return $this
     * @throws Zend_Exception
     * @throws Zend_Validate_Exception
     */
    protected function _initIconColor()
    {
        $this->_icon_color = '#FFFFFF';
        if (Version::is('PE')) {
            $this->_icon_color = $this->getBlock('border-blue')->getBorderColor();
        }

        return $this;
    }

}
