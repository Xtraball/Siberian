<?php

/**
 * Class Application_Model_Tc
 */
class Application_Model_Tc extends Core_Model_Default
{
    /**
     * @var array
     */
    public static $_types = [
        "discount" => "Discount",
        "loyaltycard" => "Loyalty Card"
    ];

    /**
     * Application_Model_Tc constructor.
     * @param array $data
     * @throws Zend_Exception
     */
    public function __construct($data = [])
    {
        parent::__construct($data);
        $this->_db_table = "Application_Model_Db_Table_Tc";
    }

    /**
     * @return array
     */
    public static function getTypes()
    {
        return self::$_types;
    }

    /**
     * @param $key
     * @param $name
     */
    public static function addType($key, $name)
    {
        self::$_types[$key] = $name;
    }

    /**
     * @param $app_id
     * @param $type
     * @return mixed
     */
    public static function findValueByType($app_id, $type)
    {
        $tc = new self();
        $tc->findByType($app_id, $type);
        return $tc->getText();
    }

    /**
     * @param string $text
     * @return $this
     * @throws Zend_Exception
     */
    public function setText($text)
    {
        $_filtered = \Siberian\Xss::sanitize($text);

        return $this->setData('text', $_filtered);
    }

    /**
     * @return mixed
     * @throws Zend_Exception
     */
    public function getText()
    {
        return \Siberian\Xss::sanitize($this->getData('text'));
    }

    /**
     * @param $app_id
     * @param $type
     * @return $this
     */
    public function findByType($app_id, $type)
    {
        $this->find(["app_id" => $app_id, "type" => $type]);
        return $this;
    }

    /**
     * @return string
     * @throws Zend_Session_Exception
     */
    public function getHtmlFilePath()
    {

        if (!file_exists(Core_Model_Directory::getCacheDirectory(true) . '/' . $this->_getFilename())) {
            $file = fopen(Core_Model_Directory::getCacheDirectory(true) . '/' . $this->_getFilename(), 'w');

            $html_code = mb_convert_encoding(html_entity_decode($this->getText()), 'HTML-ENTITIES', 'UTF-8');
            $html_a_target = "_top";
            if ($this->getSession()->isOverview) {
                $html_a_target = "_blank";
            }

            //adding or changing target of the <a>
            $doc = new Dom_SmartDOMDocument();
            $doc->loadHTML($html_code);
            $links = $doc->getElementsByTagName('a');
            foreach ($links as $item) {
                $item->setAttribute('target', $html_a_target);
            }

            $html_code = html_entity_decode($doc->saveHTML(), ENT_QUOTES, "UTF-8");

            $html = '<html><head>
                    <meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
                    <meta content="width=device-width, height=device-height, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=0" name="viewport" />
                    <meta content="black" name="apple-mobile-web-app-status-bar-style" />
                    <meta content="IE=8" http-equiv="X-UA-Compatible" />
                    <style type="text/css">
                    html, body { margin:0; padding:0; border:none; }
                    html { overflow: scroll; }
                    body { font-size: 15px; width: 100%; height: 100%; overflow: auto; -webkit-user-select : none; -webkit-text-size-adjust : none; -webkit-touch-callout: none; line-height:1; background-color:white; }
                    </style>
                </head>' . html_entity_decode($html_code) . '</html>';

            fputs($file, $html);
            fclose($file);
        }

        return Core_Model_Directory::getCacheDirectory() . '/' . $this->_getFilename();

    }

    /**
     * @return $this
     * @throws Zend_Session_Exception
     */
    public function cleanHtmlFile()
    {
        if (file_exists(Core_Model_Directory::getCacheDirectory(true) . '/' . $this->_getFilename())) {
            unlink(Core_Model_Directory::getCacheDirectory(true) . '/' . $this->_getFilename());
        }
        return $this;
    }

    /**
     * @return string
     * @throws Zend_Session_Exception
     */
    protected function _getFilename()
    {
        $key = md5($this->getUpdatedAt()) . (int)$this->getSession()->isOverview;
        return 'tc_' . $key . '_' . $this->getId() . '.html';
    }

}
