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
     * @param Application_Model_Option $option
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

        $image = (new Media_Model_Library_Image());

        if ($option->getOptionId() === 'customer_account') {
            if ($this->getApplication()->getAccountIconId()) {
                $image->find($this->getApplication()->getAccountIconId());
            } else {
                $image->find($option->getDefaultIconId());
            }
        } else if ($option->getOptionId() === 'more_items') {
            if ($this->getApplication()->getMoreIconId()) {
                $image->find($this->getApplication()->getMoreIconId());
            } else {
                $image->find($option->getDefaultIconId());
            }
        } else {
            if ($option->getIconId()) {
                $image->find($option->getIconId());
            } else {
                $image->find($option->getDefaultIconId());
            }
        }

        dbg('checking image');
        if (!$image->checkFile()) {
            // Ok we got here!
            $iii = $option->getDefaultIconId();
            dbg('$option->getDefaultIconId()', $iii);
            $image->find($iii);
        }


        $iconRelPath = $image->getRelativePath();
        $colorizable = $image->getCanBeColorized();

        if ($colorizable || $forceColorizable) {
            if (!$this->_icon_color) {
                $this->_initIconColor();
            }

            $iconRelPath = $this->getColorizedImage($iconRelPath, $this->_icon_color);
        }

        return $iconRelPath;
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
