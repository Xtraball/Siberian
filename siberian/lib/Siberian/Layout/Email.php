<?php

namespace Siberian\Layout;

use Siberian\Layout;
use Siberian\View;
use Siberian\Cache\Design as CacheDesign;

/**
 * Class Email
 * @package Siberian\Layout
 */
class Email extends Layout
{
    /**
     * @var
     */
    protected $_filename;

    /**
     * Siberian_Layout_Email constructor.
     * @param $filename
     * @param $action
     */
    public function __construct($filename, $action)
    {
        $this->_xml = simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?><xml/>');
        $this->_filename = $filename;
        $this->_action = $action;
        View::setLayout($this);
        View::setDesignType("email");

        return $this;
    }

    /**
     * @param null $useBase
     * @return $this
     */
    public function load($useBase = null)
    {

        $this->_createXml();
        $baseView = $this->getView();

        // Layout
        foreach ($this->_xml->views as $partials) {
            foreach ($partials as $key => $partial) {
                if (empty($partial->attributes()->no_ajax)) {
                    $class = (string)$partial->attributes()->class;
                    $template = (string)$partial->attributes()->template;
                    if (!empty($class) AND !empty($template)) {
                        $this->addPartial($key, $class, $template);
                    }
                }
            }
        }

        // Actions
        if (isset($this->_xml->actions)) {
            foreach ($this->_xml->actions->children() as $partial => $values) {
                if ($partial = $this->getPartial($partial)) {
                    $method = (string)$values->attributes()->name;
                    if (is_callable([$partial, $method])) {
                        $params = [];
                        foreach ($values as $key => $value) {
                            $params[$key] = (string)$value;
                        }
                        $partial->$method($params);
                    }
                }
            }
        }

        // Classes dans le body
        if (isset($this->_xml->classes)) {
            $classes = [$baseView->default_class_name];
            foreach ($this->_xml->classes->children() as $class) {
                $classes[] = $class->attributes()->name;
            }
            $baseView->default_class_name = implode_polyfill(' ', $classes);
        }


        if (isset($this->_partials['base'])) {
            $this->setBaseRender($this->_partials['base']);
        } else {
            $this->setBaseRender($this->getFirstPartial());
        }

        return $this;

    }

    /**
     * @return $this|void
     */
    public function unload()
    {
        $this->_xml = simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?><xml/>');
        $this->_filename = null;
        parent::unload();
    }

    /**
     * @param bool $useBase
     * @return null|\Siberian\SimpleXMLElement|\SimpleXMLElement
     */
    protected function _createXml($useBase = true)
    {
        $filename = $this->_filename . '.xml';
        $action = $this->_action;
        $this->_otherLayout = [];
        $keys = [];
        $email_path = CacheDesign::getBasePath("layout/default.xml", "email");
        $this->_baseDefaultLayout = simplexml_load_file($email_path, null, LIBXML_COMPACT);
        $this->_defaultLayout = $this->_baseDefaultLayout->default;

        if (isset($this->_baseDefaultLayout->$action)) {
            $this->_actionLayout = $this->_baseDefaultLayout->$action;
        } elseif (file_exists(CacheDesign::getBasePath("layout/{$filename}", "email"))) {
            $email_path = CacheDesign::getBasePath("layout/{$filename}", "email");
            $this->_baseActionLayout = simplexml_load_file($email_path);
            $this->_actionLayout = $this->_baseActionLayout->$action;
        } else {
            return $this->_defaultLayout;
        }

        // Récupération des noms des balises
        $nodes = [
            $action => $this->_actionLayout,
            'default' => $this->_defaultLayout
        ];

        $path = '/layout/' . $action . '/addLayout';
        $datas = $this->_actionLayout->xpath($path);
        foreach ($datas as $data) {
            $name = (string)$data->attributes()->name;
            if (isset($this->_baseActionLayout->$name)) {
                $nodes[$name] = $this->_otherLayout[$name] = $this->_baseActionLayout->$name;
            } elseif (isset($this->_baseDefaultLayout->$name)) {
                $nodes[$name] = $this->_otherLayout[$name] = $this->_baseDefaultLayout->$name;
            }
        }

        $path = '/layout/default/addLayout';
        $datas = $this->_baseDefaultLayout->xpath($path);
        foreach ($datas as $data) {
            $name = (string)$data->attributes()->name;
            if (isset($this->_baseDefaultLayout->$name)) {
                $nodes[$name] = $this->_otherLayout[$name] = $this->_baseDefaultLayout->$name;
            }
        }

        foreach ($nodes as $node) {
            if (empty($node)) continue;
            $children = $node->children();
            foreach ($children as $key => $child) {
                if (!in_array($key, $keys)) $keys[] = $key;
            }
        }

        // Fusion des différents XML
        foreach ($keys as $key) {

            switch ($key) {

                case "base" :
                    $this->_process($key, true);
                    break;
                case "views" :
                    $this->_process($key, true);
                    break;

                case "actions" :
                    $this->_process($key, true, true);
                    break;

                case "addLayout" :
                default :
                    break;
            }

        }

        return $this->_xml;

    }

    /**
     * @param null $name
     * @return mixed|null|string
     * @throws \Zend_Exception
     * @throws \Zend_Filter_Exception
     */
    public function render($name = null)
    {
        $datas = parent::render($name);
        $this->unload();
        return $datas;
    }

    /**
     * @param $part
     * @param $key
     * @param $value
     * @return $this
     */
    public function setContentFor($part, $key, $value)
    {
        $this
            ->getPartial($part)
            ->setData($key, $value);

        return $this;
    }

}
