<?php

use Siberian\Json;

/**
 * Class Weblink_Model_Weblink_Link
 *
 * @method Weblink_Model_Db_Table_Weblink_Link getTable()
 */
class Weblink_Model_Weblink_Link extends Core_Model_Default
{

    /**
     * Weblink_Model_Weblink_Link constructor.
     * @param array $params
     */
    public function __construct($params = array())
    {
        parent::__construct($params);
        $this->_db_table = Weblink_Model_Db_Table_Weblink_Link::class;
    }

    /**
     * @return mixed
     */
    public function getUrl($url = '', array $params = [], $locale = null)
    {
        return $this->getData('url');
    }

    /**
     * @return bool
     */
    public function getHideNavbar()
    {
        return ($this->getData('hide_navbar') === "1" ? true : false);
    }

    /**
     * @return bool
     */
    public function getUseExternalApp()
    {
        return ($this->getData('use_external_app') === "1" ? true : false);
    }

    /**
     * @return string|null
     */
    public function getPictoUrl()
    {
        $picto_path = Application_Model_Application::getImagePath() . $this->getPicto();
        $picto_base_path = Application_Model_Application::getBaseImagePath() . $this->getPicto();
        if ($this->getPicto() && file_exists($picto_base_path)) {
            return $picto_path;
        }
        return null;
    }

    /**
     * @return mixed|string
     */
    public function __toString()
    {
        parent::__toString();
        return $this->getUrl() ? $this->getUrl() : '';
    }

    /**
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options): self
    {
        return $this->setData('options', Json::encode($options));
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        try {
            $options = Json::decode($this->getData('options'));
        } catch (\Exception $e) {
            $options = [];
        }

        return $options;
    }

    /**
     * @param $webLinkId
     * @return int
     */
    public function getMaxPosition($webLinkId): int
    {
        return $this->getTable()->getMaxPosition($webLinkId);
    }

}
