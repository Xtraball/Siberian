<?php

/**
 * Class Siberian_Layout
 *
 * @todo cleanup
 */

class Siberian_Layout extends Zend_Layout
{

    const DEFAULT_CLASS_VIEW = 'Siberian_View';

    protected $_base_render = null;

    protected $_pluginClass = 'Siberian_Layout_Controller_Plugin_Layout';

    protected $_action = null;

    protected $_baseActionLayout = null;
    protected $_actionLayout = null;
    protected $_baseDefaultLayout = null;
    protected $_defaultLayout = null;
    protected $_otherLayout = array();
    public $_xml = null;
    public $_html = null;

    protected $_scripts = array("js" => array(), "css" => array(), "meta" => array());

    protected $_partials = array();

    protected $_partialshtml = array();

    protected $_is_loaded = false;

    public function __construct($options = null, $initMvc = false)
    {
        $this->_xml = simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?><xml/>');
        Siberian_View::setLayout($this);
        parent::__construct($options, $initMvc);
    }

    public static function startMvc($options = null)
    {
        if (null === self::$_mvcInstance) {
            self::$_mvcInstance = new self($options, true);
        }

        if (is_string($options)) {
            self::$_mvcInstance->setLayoutPath($options);
        } elseif (is_array($options) || $options instanceof Zend_Config) {
            self::$_mvcInstance->setOptions($options);
        }

        return self::$_mvcInstance;
    }

    public function setAction($action) {
        $this->_action = $action;
        return $this;
    }

    public function getAction() {
        return $this->_action;
    }

    public function load($use_base) {

        if(!$this->isEnabled()) return $this;

        $this->_createXml($use_base);
        $base = $this->_xml->base;

        // Récupère la vue de base et lui affecte les données (template, title, etc...)
        if($base AND isset($base->class)) {
            $baseView = $this->_getView($base->class);
            $this->setView($baseView);
        }
        else {
            $baseView = $this->getView();
        }

        if($use_base) {
            $baseView->setTitle($base->title);
            $baseView->default_class_name = $this->_action;
            $mergeFiles = APPLICATION_TYPE == "mobile" && APPLICATION_ENV == "production";

            $scripts = $base->scripts;

            // Scripts JS
            $jsToMerge = array();
            $cache = Zend_Registry::isRegistered('cache') ? Zend_Registry::get('cache') : null;
            $cacheId = 'js_'.APPLICATION_TYPE;

            if(!$mergeFiles OR !$cache OR !$cache->load($cacheId)) {

                foreach($scripts->js as $files) {
                    foreach($files as $file) {

                        if($file->attributes()->link) {
                            $link = (String) $file->attributes()->link;
                            $link = Siberian_Cache_Design::getPath($link);

                            $jsToMerge["local"][] = $link;
                        } else if($file->attributes()->href) {
                            $link = (String) $file->attributes()->href;

                            $jsToMerge["external"][] = $link;
                        }

                        if($file->attributes()->folder) {
                            $folder = (String) $file->attributes()->folder;
                            $files = Siberian_Cache_Design::searchForFolder($folder);

                            foreach($files as $basename => $fullpath) {
                                $pathinfo = pathinfo($fullpath);
                                if(empty($pathinfo["extension"]) OR $pathinfo["extension"] != "js") {
                                    continue;
                                }

                                $this->_scripts['js'][] = $fullpath;
                                $jsToMerge["local"][] = $fullpath;
                            }

                        }
                    }

                    if($cache) {
                        $cache->save($jsToMerge, $cacheId);
                    }

                }
            }




            if(empty($jsToMerge) AND $cache) {
                $jsToMerge = $cache->load($cacheId);
            }

            $js_file = Core_Model_Directory::getCacheDirectory()."/js_".APPLICATION_TYPE.".js";

            if($mergeFiles) {
                if(!file_exists(Core_Model_Directory::getBasePathTo($js_file))) {
                    // Merging javascript files
                    $js = fopen(Core_Model_Directory::getBasePathTo($js_file), "w");
                    foreach($jsToMerge["local"] as $file) {
                        fputs($js, file_get_contents(Core_Model_Directory::getBasePathTo($file)).PHP_EOL);
                    }
                    fclose($js);
                }

                // Appending the JS files to the view
                $js_file .= "?".filemtime(Core_Model_Directory::getBasePathTo($js_file));
                if(preg_match("#^app/#", $js_file)) {
                    $js_file = "/".$js_file;
                }
                $this->_scripts['js'][] = $js_file;
                $baseView->headScript()->appendFile($js_file);


            } else {
                foreach($jsToMerge["local"] as $file) {
                    $file .= "?".filemtime(Core_Model_Directory::getBasePathTo($file));
                    if(preg_match("#^app/#", $file)) {
                        $file = "/".$file;
                    }
                    $this->_scripts['js'][] = $file;
                    $baseView->headScript()->appendFile($file);
                }
            }

            if(!empty($jsToMerge["external"])) {
                foreach($jsToMerge["external"] as $external_js) {
                    $this->_scripts['js'][] = $external_js;
                    $baseView->headScript()->appendFile($external_js);
                }
            }

            // Scripts CSS
            $cssToMerge = array();
            $cacheId = 'css_'.APPLICATION_TYPE;
            if(!$mergeFiles OR !$cache OR !$cache->load($cacheId)) {
                foreach($scripts->css as $files) {
                    foreach($files as $file) {
                        if($file->attributes()->link) {
                            $link = (String) $file->attributes()->link;
                            $link = Siberian_Cache_Design::getPath($link);

                            $cssToMerge["local"][] = $link;
                        }
                        else {
                            $link = (String) $file->attributes()->href;

                            $cssToMerge["external"][] = $link;
                        }

                        if($file->attributes()->folder) {

                            $folder = (String) $file->attributes()->folder;
                            $files = Siberian_Cache_Design::searchForFolder($folder);

                            foreach($files as $basename => $fullpath) {
                                $pathinfo = pathinfo($fullpath);
                                if(empty($pathinfo["extension"]) OR $pathinfo["extension"] != "css") {
                                    continue;
                                }

                                $this->_scripts['css'][] = $fullpath;
                                $cssToMerge["local"][] = $fullpath;
                            }

                        }
                    }
                }

                if($cache) {
                    $cache->save($cssToMerge, $cacheId);
                }
            }

            if(empty($cssToMerge) AND $cache) {
                $cssToMerge = $cache->load($cacheId);
            }

            $css_file = Core_Model_Directory::getCacheDirectory()."/css_".APPLICATION_TYPE.".css";
            $base_css_file = Core_Model_Directory::getCacheDirectory(true)."/css_".APPLICATION_TYPE.".css";
            if($mergeFiles) {

                if(!file_exists($base_css_file)) {
                    // Merging css files
                    $css = fopen($base_css_file, "w");
                    foreach($cssToMerge["local"] as $file) {
                        fputs($css, file_get_contents(Core_Model_Directory::getBasePathTo($file)).PHP_EOL);
                    }
                    fclose($css);
                }

                // Appending the CSS files to the view
                $css_file .= "?".filemtime(Core_Model_Directory::getBasePathTo($css_file));
                if(preg_match("#^app/#", $css_file)) {
                    $css_file = "/".$css_file;
                }
                $this->_scripts['css'][] = $css_file;
                $baseView->headLink()->appendStylesheet($css_file, 'all');

            } else {

                foreach($cssToMerge["local"] as $file) {
                    $file .= "?".filemtime(Core_Model_Directory::getBasePathTo($file));
                    if(preg_match("#^app/#", $file)) {
                        $file = "/".$file;
                    }
                    $this->_scripts['css'][] = $file;
                    $baseView->headLink()->appendStylesheet($file, 'all');
                }

            }

            if(!empty($cssToMerge["external"])) {
                foreach($cssToMerge["external"] as $external_css) {
                    $this->_scripts['css'][] = $external_css;
                    $baseView->headLink()->appendStylesheet($external_css, 'all');
                }
            }



            // Balises meta
            foreach($base->metas as $metas) {
                foreach($metas as $key => $meta) {
                    $type = $meta->attributes()->type;
                    $baseView->addMeta($type, $key, $meta->attributes()->value);
                    $this->_scripts['meta'][] = array(
                        'type' => (string) $type,
                        'key' => (string) $key,
                        'value' => (string) $meta->attributes()->value
                    );
                }
            }

            // Layout du template de base
            if(count($this->_xml->{$base->template}->views)) {
                foreach ($this->_xml->{$base->template}->views as $partials) {
                    foreach ($partials as $key => $partial) {
                        $class = (string)$partial->attributes()->class;
                        $template = (string)$partial->attributes()->template;
                        if (!empty($class) AND !empty($template)) {
                            $this->addPartial($key, $class, $template);
                        }
                    }
                }
            }
        }

        // Layout
        foreach($this->_xml->views as $partials) {
            foreach($partials as $key => $partial) {
                if($use_base OR (!$use_base AND empty($partial->attributes()->no_ajax))) {
                    $class = (string) $partial->attributes()->class;
                    $template = (string) $partial->attributes()->template;
                    if(!empty($class) AND !empty($template)) {
                        $this->addPartial($key, $class, $template);
                    }
                }
            }
        }

        // Actions
        if(isset($this->_xml->actions)) {
            foreach($this->_xml->actions->children() as $partial => $values) {

                if($partial = $this->getPartial($partial)) {
                    $method = (string) $values->attributes()->name;
                    if(is_callable(array($partial, $method))) {
                        $params = array();
                        if(count($values) == 1) {
                            foreach($values as $key => $value) {
                                $params = (string) $value;
                            }
                        }
                        else {
                            foreach($values as $key => $value) {
                                $params[$key] = (string) $value;
                            }
                        }

                        $partial->$method($params);
                    }
                }
            }
        }

        // Classes dans le body
        if(isset($this->_xml->classes)) {
            $classes = array($baseView->default_class_name);
            foreach($this->_xml->classes->children() as $class) {
                $classes[] = $class->attributes()->name;
            }
            $baseView->default_class_name = implode(' ', $classes);
        }

        if($use_base) {
            $this->setBaseRender("base", "{$base->template}.phtml", null);
        } else if(isset($this->_partials['base'])) {
            $this->setBaseRender($this->_partials['base']);
        } else {
            $this->setBaseRender($this->getFirstPartial());
        }

        return $this;

    }

    public function unload() {
        $this->_base_render = null;
        $this->_partials = array();
        $this->_partialshtml = array();

        $this->_baseActionLayout = null;
        $this->_actionLayout = null;
        $this->_baseDefaultLayout = null;
        $this->_defaultLayout = null;
        $this->_otherLayout = array();
        $this->_xml = simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?><xml/>');
        $this->_html = null;
        $this->_is_loaded = false;

        Siberian_View::setLayout($this);

        return $this;

    }

    /**
     * @todo create a standalone file not using Siberian_Layout_Email
     * which can be confusing
     *
     * @param $filename
     * @param $nodename
     * @return Siberian_Layout_Email
     */
    public function loadPartial($filename, $nodename) {
        $layout = new Siberian_Layout_Email($filename, $nodename);
        Siberian_View::setDesignType("desktop");
        $layout->load();
        return $layout;
    }

    /**
     * @param $filename
     * @param $nodename
     * @return Siberian_Layout_Email
     */
    public function loadEmail($filename, $nodename) {
        $layout = new Siberian_Layout_Email($filename, $nodename);
        $layout->load();
        return $layout;
    }

    /**
     * @param $key
     * @param null $template
     * @param null $view
     * @return mixed|null|Zend_View|Zend_View_Interface
     */
    public function setBaseRender($key, $template = null, $view = null) {

        if($key instanceof Zend_View) {
            $template = $key->getTemplate();
            $view = $key;
        }
        else {
            $view = is_null($view) ? $this->getView() : $this->_getView($view);
            $view->setTemplate($template);
        }
        if(preg_match('/.phtml$/', $template)) $this->disableInflector();

        parent::setLayout($template);
        $this->_base_render = $view;
        $this->_is_loaded = true;

        return $view;
    }

    public function getBaseRender() {
        return $this->_base_render;
    }

    public function addPartial($key, $view, $template) {
        $this->_partials[$key] = $this->_getView($view)->setTemplate($template);
        return $this->_partials[$key];
    }

    public function getPartial($key) {
        return isset($this->_partials[$key]) ? $this->_partials[$key] : null;
    }

    public function getFirstPartial() {
        return reset($this->_partials);
    }

    public function getPartialHtml($key) {
        if(!isset($this->_partialshtml[$key]) AND $this->getPartial($key)) {
            $this->_partialshtml[$key] = $this->getPartial($key)->render($this->getPartial($key)->getTemplate());
        }

        return isset($this->_partialshtml[$key]) ? $this->_partialshtml[$key] : null;
    }

    public function setPartialHtml($key, $html) {
        $this->_partialshtml[$key] = $html;
        return $this;
    }

    public function getHtml() {
        return implode(' ', $this->_partialshtml);
    }

    public function setHtml($html) {
        $this->_html = $html;
        $this->_is_loaded = true;
        return $this;
    }

    public function render($name = null) {
        if(is_null($this->_html)) {

            $this->renderPartials();

            $name = $this->_base_render->getTemplate();

            if ($this->inflectorEnabled() && (null !== ($inflector = $this->getInflector()))) {
                try {
                    $name = $this->_inflector->filter(array('script' => $name));
                } catch(Exception $e) {
                    $message = "Unable to find a template for the {$this->_action} action\n\n".print_r($e, true);
                    Zend_Registry::get("logger")->sendException($message, "layout_", false);
                    return "";
                }
            }

            $view = $this->_base_render;

            if (null !== ($path = $this->getViewScriptPath())) {
                if (method_exists($view, 'addScriptPath')) {
                    $view->addScriptPath($path);
                } else {
                    $view->setScriptPath($path);
                }
            }
            elseif (null !== ($path = $this->getViewBasePath())) {
                $view->addBasePath($path, $this->_viewBasePrefix);
            }

            $this->_html = $view->render($name);

        }

        return $this->_html;

    }

    public function toJson() {

        /**
         * @unused
         * $baseView = $this->_base_render;
         * */

        $this->renderPartials();

        $data = array(
            "scripts" => $this->_scripts,
            "partials" => $this->_partialshtml
        );

        return Zend_Json::encode($data);

    }

    public function isLoaded() {
        return $this->_is_loaded;
    }

    /**
     * @return type
     */
    protected function _createXml($use_base) {

        // Définition des variables
        $action = $this->_action;
        $module = current(explode('_', $action));
        $filename = $module.'.xml';
        $this->_otherLayout = array();

        $keys = array();
        if($use_base) {
            $front_xml = Siberian_Cache_Design::getBasePath("/layout/front.xml");
            $this->_baseDefaultLayout = new SimpleXMLElement(file_get_contents($front_xml));
            $this->_defaultLayout = $this->_baseDefaultLayout->default;
        }

        if($use_base AND isset($this->_baseDefaultLayout->$action)) {
            $this->_actionLayout = $this->_baseDefaultLayout->$action;
        }
        elseif(file_exists(Siberian_Cache_Design::getBasePath("/layout/{$filename}"))) {
            $layout_xml = Siberian_Cache_Design::getBasePath("/layout/{$filename}");
            $this->_baseActionLayout = new SimpleXMLElement(file_get_contents($layout_xml));
            $this->_actionLayout = $this->_baseActionLayout->$action;
        }
        else {
            return $this->_defaultLayout;
        }

        // Récupération des noms des balises
        $nodes = array(
            $action => $this->_actionLayout,
            'default' => $this->_defaultLayout
        );

        $path = '/layout/'.$action.'/addLayout';
        $datas = $this->_actionLayout->xpath($path);
        foreach($datas as $data) {
            $name = (string) $data->attributes()->name;
            if(isset($this->_baseActionLayout->$name)) {
                $nodes[$name] = $this->_otherLayout[$name] = $this->_baseActionLayout->$name;
            }
            elseif(isset($this->_baseDefaultLayout->$name)) {
                $nodes[$name] = $this->_otherLayout[$name] = $this->_baseDefaultLayout->$name;
            }
        }

        if($use_base) {
            $path = '/layout/default/addLayout';
            $datas = $this->_baseDefaultLayout->xpath($path);
            foreach($datas as $data) {
                $name = (string) $data->attributes()->name;
                if(isset($this->_baseDefaultLayout->$name)) {
                    $nodes[$name] = $this->_otherLayout[$name] = $this->_baseDefaultLayout->$name;
                }
            }
        }

        $path = '/layout/'.$action.'/removeLayout';
        $datas = $this->_actionLayout->xpath($path);
        foreach($datas as $data) {
            $name = (string) $data->attributes()->name;
            if(isset($nodes['default']->views->$name)) {
                unset($nodes['default']->views->$name);
            }
        }

        if($use_base) {
            $path = '/layout/default/removeLayout';
            $datas = $this->_baseDefaultLayout->xpath($path);
            foreach($datas as $data) {
                $name = (string) $data->attributes()->name;
                if(isset($nodes['default']->views->$name)) {
                    unset($nodes['default']->views->$name);
                }
            }
        }

        foreach($nodes as $node) {
            if(empty($node)) continue;
            $children = $node->children();
            foreach($children as $key => $child) {
                if(!in_array($key, $keys)) $keys[] = $key;
            }
        }

        // Fusion des différents XML
        foreach($keys as $key) {
            switch($key) {

                case "base" :
                    if($use_base) $this->_process($key, $use_base);
                break;
                case "views" :
                case "layout" :
                case "layout_col-left" :
                case "layout_col-right" :
                    $this->_process($key, $use_base);
                break;

                case "actions" :
                case "classes" :
                    $this->_process($key, $use_base, true);
                break;

                case "addLayout" :
                default :
                break;
            }

        }

        return $this->_xml;

    }

    protected function _process($key, $use_base, $forceAddNode = false) {

        $child = $this->_xml->addChild($key);

        $path = '/layout/'.$this->_action.'/'.$key;
        $datas = $this->_actionLayout->xpath($path);
        foreach($datas as $data) {
            $this->_mergeXml($data, $child, $forceAddNode);
        }

        if($use_base) {
            foreach($this->_otherLayout as $name => $node) {
                $path = '/layout/'.$name.'/'.$key;
                $datas = $node->xpath($path);
                foreach($datas as $data) {
                    $this->_mergeXml($data, $child, $forceAddNode);
                }
            }

            $path = '/layout/default/'.$key;
            $datas = $this->_defaultLayout->xpath($path);
            foreach($datas as $data) {
                $this->_mergeXml($data, $child, $forceAddNode);
            }
        }

    }

    protected function _mergeXml($datas, $node, $forceAddNode = false, $i = 0) {

        $j = 0;
        foreach($datas as $key => $value) {

            if((bool) $value->children()) {

                if(!isset($node->$key) || $forceAddNode) {
                    $child = $node->addChild($key);
                    if((bool) $value->attributes()) {
                        foreach($value->attributes() as $attr_code => $attr_value) {
                            $child->addAttribute($attr_code, $attr_value);
                        }
                    }
                }
                else {
                    $child = $node->$key;
                }

                $this->_mergeXml($value, $child, $forceAddNode, $i+1);

            }
            else {
                if(!isset($node->$key)) {
                    $node->$key = $value;
                }
            }
        }

    }

    /** @migration template */
    protected function renderPartials() {
        foreach($this->_partials as $key => $_partial) {
            if(!isset($this->_partialshtml[$key])) {
                $this->_partialshtml[$key] = $_partial->render($_partial->getTemplate());
            }
        }

        return $this;
    }

    protected function _renderPartial(array $_partial) {
        return $this->_getView($_partial['view'])->render($_partial['template']);
    }

    protected function _getView($class) {

        $classname = self::DEFAULT_CLASS_VIEW;
        if($class) {
            $classname = implode('_', array_map('ucwords', explode('_', $class)));
        }
        try {
            $object = new $classname();
        } catch(Exception $e) {
            Zend_Debug::dump($e);
            die;
        }
        $object->setScriptPath($this->getView()->getScriptPaths());
        return $object;

    }

}
