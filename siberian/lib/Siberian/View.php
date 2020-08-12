<?php

namespace Siberian;

use Core_Model_Default;
use Core_Model_Lib_String;
use \Siberian\Cache\Design as Design;

/**
 * Class View
 * @package Siberian
 */
class View extends \Zend_View
{
    public $_file = null;
    public $_filter = [];

    protected static $_request;
    protected static $_layout;
    protected static $_designtype = null;

    protected $_template;
    protected $_metas = [];
    protected $_data;

    /**
     * @param $layout
     */
    public static function setLayout($layout)
    {
        self::$_layout = $layout;
    }

    /**
     * @return mixed
     */
    public function getLayout()
    {
        return self::$_layout;
    }

    /**
     * @param $type
     */
    public static function setDesignType($type)
    {
        self::$_designtype = $type;
    }

    /**
     * @return null
     */
    public static function getDesignType()
    {
        return self::$_designtype;
    }

    /**
     * @param $request
     */
    public static function setRequest($request)
    {
        self::$_request = $request;
    }

    /**
     * @return mixed
     */
    public function getRequest()
    {
        return self::$_request;
    }

    /**
     * @param $template
     * @return $this
     */
    public function setTemplate($template)
    {
        $this->_template = $template;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTemplate()
    {
        return $this->_template;
    }

    /**
     * @param $tag
     * @param $key
     * @param $content
     * @return $this
     */
    public function addMeta($tag, $key, $content)
    {
        if (empty($tag)) $tag = 'name';
        $meta = new Core_Model_Default([
            'tag' => (string)$tag,
            'key' => $key,
            'content' => (string)$content
        ]);
        $this->_metas[$key] = $meta;
        return $this;
    }

    /**
     * @return array
     */
    public function getMetas()
    {
        return $this->_metas;
    }

    /**
     * @param $key
     * @return $this
     */
    public function unsMeta($key)
    {
        if (!empty($this->_metas[$key])) unset($this->_metas[$key]);
        return $this;
    }

    /**
     * @return false|int
     */
    public function isOldBrowser()
    {
        return preg_match('/(?i)msie [5-8]/', $this->getRequest()->getHeader('user_agent'));
    }

    /**
     * @param null $name
     * @return string
     */
    public function renderHtml($name = null)
    {
        $name = is_null($name) ? $this->getTemplate() : $name;

        return $this->render($name);
    }

    /**
     * @param $key
     * @return mixed
     */
    public function getPartial($key)
    {
        return $this->getLayout()->getPartial($key);
    }

    /**
     * @param $key
     * @return mixed
     */
    public function getPartialHtml($key)
    {
        $this->getLayout()->getPartialHtml($key);
        return $this->getLayout()->getPartialHtml($key);
    }

    /**
     * @param $key
     * @param $class
     * @param $template
     * @return mixed
     */
    public function createPartialHtml($key, $class, $template)
    {
        $this->getLayout()->addPartial($key, $class, $template);
        return $this->getPartialHtml($key);
    }

    /**
     * @param string $path
     * @param string $classPrefix
     * @return $this|\Zend_View_Abstract
     */
    public function setBasePath($path, $classPrefix = 'Siberian_View')
    {
        $path = rtrim($path, '/');
        $path = rtrim($path, '\\');
        $path .= DIRECTORY_SEPARATOR;
        $classPrefix = rtrim($classPrefix, '_') . '_';
        $this->setScriptPath($path);
        $this->setHelperPath($path . 'helpers', $classPrefix . 'Helper');
        $this->setFilterPath($path . 'filters', $classPrefix . 'Filter');
        return $this;
    }

    /**
     * @param string $path
     * @param string $classPrefix
     * @return $this|\Zend_View_Abstract
     */
    public function addBasePath($path, $classPrefix = 'Siberian_View')
    {
        $path = rtrim($path, '/');
        $path = rtrim($path, '\\');
        $path .= DIRECTORY_SEPARATOR;
        $classPrefix = rtrim($classPrefix, '_') . '_';
        $this->addScriptPath($path);
        $this->addHelperPath($path . 'helpers', $classPrefix . 'Helper');
        $this->addFilterPath($path . 'filters', $classPrefix . 'Filter');
        return $this;
    }

    /**
     * @param string $name
     * @return string
     */
    public function toHtml($name = '')
    {
        if (empty($name)) $name = $this->getTemplate();

        return $this->render($name);
    }

    /**
     * @param string $method
     * @param array $args
     * @return mixed|string
     */
    public function __call($method, $args)
    {
        $accessor = substr($method, 0, 3);
        $magicKeys = ['set', 'get', 'uns', 'has'];

        if (in_array($accessor, $magicKeys)) {
            $key = Core_Model_Lib_String::camelize(substr($method, 3));
            $method = $accessor . 'Data';
            $value = isset($args[0]) ? $args[0] : null;
            return call_user_func([$this, $method], $key, $value);
        }

        return parent::__call($method, $args);
    }

    /**
     * @param $key
     * @param null $value
     * @return $this
     */
    public function addData($key, $value = null)
    {
        if (is_null($value)) {
            $values = $key;
            foreach ($values as $key => $value) {
                $this->setData($key, $value);
            }
        } else {
            $this->$key = $value;
        }
        return $this;
    }

    /**
     * @param $key
     * @param null $value
     * @return $this
     */
    public function setData($key, $value = null)
    {
        if (is_array($key)) {
            if (isset($this->_data['id'])) {
                $key['id'] = $this->_data['id'];
            }
            $this->_data = $key;
        } else {
            $this->_data[$key] = $value;
        }
        return $this;
    }

    /**
     * @param null $key
     * @return $this
     */
    public function unsetData($key = null)
    {
        if (is_null($key)) {
            $this->_data = [];
        } else {
            unset($this->_data[$key]);
        }
        return $this;
    }

    /**
     * @param string $key
     * @return null|string
     */
    public function getData($key = '')
    {
        if ($key === '') {
            return $this->_data;
        } else if (isset($this->_data[$key]) && !is_null($this->_data[$key])) {
            return is_string($this->_data[$key]) ? stripslashes($this->_data[$key]) : $this->_data[$key];
        } else {
            return null;
        }
    }

    /**
     * @param $key
     * @return bool
     */
    public function hasData($key)
    {
        return isset($this->_data[$key]);
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->_data);
    }

    /**
     * @param string $name
     * @return string\void
     * @throws \Zend_View_Exception
     */
    protected function _script($name)
    {
        /** Siberian local inheritance test */
        $base_path = Design::getBasePath("template/{$name}", self::getDesignType());
        if (is_readable($base_path)) {
            return $base_path;
        }

        /** Otherwise use the parent behavior */
        return parent::_script($name);
    }
}
