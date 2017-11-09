<?php

class Core_Model_Url extends Core_Model_Default
{

    /**
     * @param string $url
     * @param array $params
     * @param null $locale
     * @return array|mixed|string
     */
    public static function create($url = '', array $params = array(), $locale = null) {

        $request = Zend_Controller_Front::getInstance()->getRequest();

        $exclude = array();

        $setLocale = false;
        $encode = false;
        if(!empty($params['encode'])) {
            $encode = true;
            unset($params['encode']);
        }

        if(!is_null($locale) AND in_array($locale, Core_Model_Language::getLanguageCodes())) {
            $request->addLanguageCode($locale);
        }

        $url = str_replace($request->getBaseUrl(), '', $url);
        $url = trim($url, '/');
        $url = explode('/', $url);
        $url = array_diff($url, $exclude);

        $count = count($url);
        if($count == 0) $url = array_fill(0, 3, '');
        while($count > 0 && $count++ < 3) {
            $url[] = 'index';
        }
        $url = array_values($url);

        $url = array(
            'module' => $url[0],
            'controller' => $url[1],
            'action' => $url[2]
        );

        if(!empty($params)) {
            if(!empty($params['module'])) unset($params['module']);
            if(!empty($params['controller'])) unset($params['controller']);
            if(!empty($params['action'])) unset($params['action']);
            $url = array_merge($url, $params);
        }

        $reset = empty($params['keep_params']);

        $router = Zend_Controller_Front::getInstance()->getRouter();
        $url = $router->assemble($url, 'default', $reset, false);

        $request->setLanguageCode(null);

        return $url;

    }

    public static function createCustom($host, $url = '', array $params = array(), $locale = null) {
        $front = Zend_Controller_Front::getInstance();
        $request = $front->getRequest();
        $default_base_url = $request->getBaseUrl();
        $request->setBaseUrl($host);

        $url = self::create($url, $params, $locale);

        $request->setBaseUrl($default_base_url);

        return $url;

    }

    public static function current($withParams = true, $locale = null) {

        $front = Zend_Controller_Front::getInstance();
        $request = $front->getRequest();

        $url = implode('/', array(
            $request->getModuleName(),
            $request->getControllerName(),
            $request->getActionName()
        ));

        if($withParams) {
            $params = $request->getParams();
            $remove = array('module', 'controller', 'action');
            foreach($params as $key => $param) {
                if(in_array($param, $remove)) {
                    unset($params[$key]);
                }
            }
        }
        else {
            $params = array();
        }

        # Sanitize data, prevents XSS injection, Siberian 5.0
        foreach($params as &$param) {
            $param = filter_var($param, FILTER_SANITIZE_STRING);
        }

        return self::create($url, $params, $locale);

    }

    public static function createPath($uri, $params = array(), $locale = null) {

        $url = self::create($uri, $params, $locale);

        $request = Zend_Controller_Front::getInstance()->getRequest();
        $url = str_replace($request->getBaseUrl(), '', $url);

        return $url;

    }

    public static function checkCname($url) {
        $ip = $_SERVER['SERVER_ADDR'];
        $foreign_ip = gethostbyname($url);
        return $ip == $foreign_ip;
    }

}