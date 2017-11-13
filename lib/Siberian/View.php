<?php

/**
 * Class Siberian_View
 *
 * @todo cleanup, refacto
 */
class Siberian_View extends Zend_View
{
    public $_file = null;
    public $_filter = array();

    protected static $_request;
    protected static $_layout;
    protected static $_designtype = null;

    protected $_template;
    protected $_metas = array();
    protected $_data;

    public static function setLayout($layout) {
        self::$_layout = $layout;
    }

    public function getLayout() {
        return self::$_layout;
    }

    public static function setDesignType($type) {
        self::$_designtype = $type;
    }

    public static function getDesignType() {
        return self::$_designtype;
    }

    public static function setRequest($request) {
        self::$_request = $request;
    }

    public function getRequest() {
        return self::$_request;
    }

    public function setTemplate($template) {
        $this->_template = $template;
        return $this;
    }

    public function getTemplate() {
        return $this->_template;
    }

    public function addMeta($tag, $key, $content) {
        if(empty($tag)) $tag = 'name';
        $meta = new Core_Model_Default(array(
            'tag' => (string) $tag,
            'key' => $key,
            'content' => (string) $content
        ));
        $this->_metas[$key] = $meta;
        return $this;
    }

    public function getMetas() {
        return $this->_metas;
    }

    public function unsMeta($key) {
        if(!empty($this->_metas[$key])) unset($this->_metas[$key]);
        return $this;
    }

    public function isOldBrowser() {
        return preg_match('/(?i)msie [5-8]/',$this->getRequest()->getHeader('user_agent'));
    }

    public function renderHtml($name = null) {
        $name = is_null($name) ? $this->getTemplate() : $name;

        return $this->render($name);
    }

    public function getPartial($key) {
        return $this->getLayout()->getPartial($key);
    }

    public function getPartialHtml($key) {
        return $this->getLayout()->getPartialHtml($key);
    }

    public function createPartialHtml($key, $class, $template) {
        $this->getLayout()->addPartial($key, $class, $template);
        return $this->getPartialHtml($key);
    }

    public function setBasePath($path, $classPrefix = 'Siberian_View') {
        $path        = rtrim($path, '/');
        $path        = rtrim($path, '\\');
        $path       .= DIRECTORY_SEPARATOR;
        $classPrefix = rtrim($classPrefix, '_') . '_';
        $this->setScriptPath($path);
        $this->setHelperPath($path . 'helpers', $classPrefix . 'Helper');
        $this->setFilterPath($path . 'filters', $classPrefix . 'Filter');
        return $this;
    }

    public function addBasePath($path, $classPrefix = 'Siberian_View') {
        $path        = rtrim($path, '/');
        $path        = rtrim($path, '\\');
        $path       .= DIRECTORY_SEPARATOR;
        $classPrefix = rtrim($classPrefix, '_') . '_';
        $this->addScriptPath($path);
        $this->addHelperPath($path . 'helpers', $classPrefix . 'Helper');
        $this->addFilterPath($path . 'filters', $classPrefix . 'Filter');
        return $this;
    }

    public function toHtml($name = '') {
        if(empty($name)) $name = $this->getTemplate();

        return $this->render($name);
    }

    public function __call($method, $args)
    {
        $accessor = substr($method, 0, 3);
        $magicKeys = array('set', 'get', 'uns', 'has');

        if(in_array($accessor, $magicKeys)) {
            $key = Core_Model_Lib_String::camelize(substr($method,3));
            $method = $accessor.'Data';
            $value = isset($args[0]) ? $args[0] : null;
            return call_user_func(array($this, $method), $key, $value);
        }

        return parent::__call($method, $args);
    }

    public function addData($key, $value=null)
    {
        if(is_null($value)) {
            $values = $key;
            foreach($values as $key => $value) {
                $this->setData($key, $value);
            }
        }
        else {
            $this->$key = $value;
        }
        return $this;
    }

    public function setData($key, $value=null) {
        if(is_array($key)) {
            if(isset($this->_data['id'])) {
                $key['id'] = $this->_data['id'];
            }
            $this->_data = $key;
        } else {
            $this->_data[$key] = $value;
        }
        return $this;
    }

    public function unsetData($key=null)
    {
        if (is_null($key)) {
            $this->_data = array();
        } else {
            unset($this->_data[$key]);
        }
        return $this;
    }

    public function getData($key='')
    {
        if ($key==='') {
            return $this->_data;
        }
        elseif(isset($this->_data[$key]) AND !is_null($this->_data[$key])) {
            return is_string($this->_data[$key]) ? stripslashes($this->_data[$key]) : $this->_data[$key];
        }
        else {
            return null;
        }
    }

    public function hasData($key) {
        return isset($this->_data[$key]);
    }

    public function isEmpty() {
        return empty($this->_data);
    }

    /**
     * Finds a view script from the available directories.
     *
     * @override
     * @param string $name The base name of the script.
     * @return void
     */
    protected function _script($name)
    {
        /** Siberian local inheritance test */
        $base_path = Siberian_Cache_Design::getBasePath("template/{$name}", self::getDesignType());
        if(is_readable($base_path)) {
            return $base_path;
        }

        /** Otherwise use the parent behavior */
        return parent::_script($name);
    }


}
