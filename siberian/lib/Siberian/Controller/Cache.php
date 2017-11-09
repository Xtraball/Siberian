<?php

class Siberian_Controller_Cache extends Zend_Controller_Action {

    /**
     * Bypass the action
     * @var boolean
     */
    protected $_cancelAction = false;
    protected $_config = null;
    /**
     * @var Zend_Log
     */
    protected $_logger;
    protected $cacheName = null;
    protected $cacheId = null;
    protected $disabled = false;
    /**
     * @var Zend_Cache_Core
     */
    protected $cache_global;
    protected $getStatus = null;
    /**
     * Pour qu'au niveau du postDispatch il sache
     * @var boolean
     */
    protected $_cacheable = false;

    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array()){
        //Tous les dispatch (meme les re-dispatch invoqués par les helper (actions,vues,forward) reconstruisent
        parent::__construct($request, $response, $invokeArgs);

        $this->_logger = Zend_Controller_Front::getInstance()->getParam("bootstrap")->getResource("Log");

        $this->cache_global = Zend_Controller_Front::getInstance()->getParam("bootstrap")->getResource("CacheManager")->getCache('global');
    }


    /**
     * Retourne la config du cache pour cette action ou false
     * @return mixed Zend_Config|boolean
     */
    protected function getConfig(){
        if($this->_config === null){
            $name = $this->getCacheName();
            $this->_config = isset(Zend_Registry::get('config')->cache->$name) ? Zend_Registry::get('config')->cache->$name:false;
        }

        return $this->_config;
    }

    /**
     * Set if the action is bypassed
     *
     * @param  boolean $bypass
     * @return void
     */
    public function setCancelAction($bypass){
        $this->_cancelAction = $bypass;
    }

    /**
     * Dispatch the requested action
     *
     * @param string $action Method name of action
     * @return void
     */
    public function dispatch($action){

        // Notify helpers of action preDispatch state
        $this->_helper->notifyPreDispatch();
        $this->preDispatch();



        // OuiCar $this->_cancelAction, bypass the action (use the cache)
        if ($this->getRequest()->isDispatched()) {
            if(!$this->_cancelAction) {
                if (null === $this->_classMethods) {
                    $this->_classMethods = get_class_methods($this);
                }

                // If pre-dispatch hooks introduced a redirect then stop dispatch
                // @see ZF-7496
                if (!($this->getResponse()->isRedirect())) {
                    // preDispatch() didn't change the action, so we can continue
                    if ($this->getInvokeArg('useCaseSensitiveActions') || in_array($action, $this->_classMethods)) {
                        if ($this->getInvokeArg('useCaseSensitiveActions')) {
                            trigger_error('Using case sensitive actions without word separators is deprecated; please do not rely on this "feature"');
                        }
                        $this->$action();
                    } else {
                        $this->__call($action, array());
                    }
                }
                $this->postDispatch();
            }
            /// Executée si dispatchée
        }

        // whats actually important here is that this action controller is
        // shutting down, regardless of dispatching; notify the helpers of this
        // state
        $this->_helper->notifyPostDispatch();
    }

    /**
     *  Si on trouves le cache, on bypass l'action,
     *  et on assigne a la vue les variables précédemments sauvegardés
     *
     */
    public function preDispatch(){
        /// Executée quoi qu'il arrive.

        // Il y a une config donc un cache possible
        if($this->getConfig()){
            if(
                $this->hasOnlyIgnoredGets() && //pas d'autres get que ceux ignorés
                $this->getRequest()->isDispatched() && //la requete n'a pas été modifiée par un pre_dispatch d'un helper d'action
                !$this->getRequest()->isPost() && /// Pas de requete en POST
                !$this->disabled
            ){
                $this->_cacheable = true; //for the postDispatch

                $this->view->cacheObject = array(
                    'lifetime' => $this->getConfig()->lifetime,
                    'id' => $this->getCacheId(),
                    'name' => $this->getCacheName()
                );

                if( ($result = $this->cache_global->load($this->getCacheId())) !== false){
                    $this->getResponse()->setHeader('Za-Cache', 'HIT');
                    $this->setCancelAction(true);
                    /// Re-Assign the cached vars to the view
                    $this->view->assign($result);
                }

            }else{
                $this->getResponse()->setHeader('Za-Cache', 'PASS');
            }
        }
    }

    /**
     *
     * Si on a pas le cache, on arrive donc au postDispatch()
     * Du coup on sauvegarde les variables de vue
     *
     */
    public function postDispatch(){
        if($this->_cacheable){

            if(
                !$this->getResponse()->isRedirect() &&
                $this->getRequest()->isDispatched()
            ) {
                $this->getResponse()->setHeader('Za-Cache', 'MISS');
                $view_vars = $this->view->getVars();
                unset($view_vars['cacheObject']);

                $this->cache_global->save($view_vars, $this->getCacheId(), array($this->getCacheName()), $this->getConfig()->lifetime);
            }else{
                $this->getResponse()->setHeader('Za-Cache', 'PASS');
            }
        }
    }

    /**
     * Get the cache id for the current action
     *
     * @param Boolean $cacheName true for config name
     * @return String
     */
    public function getCacheId() {
        if($this->cacheId == null) {
            $cacheId = $this->getCacheName().'_';

            $params = isset($this->getConfig()->params) ? $this->getConfig()->params : array(); /// Default
            $_params = array();
            foreach($params as $key) {
                $result = $this->getRequest()->getParam($key, '_undefined_');
                $_params[] = $key.'_'.$result;
            }
            $cacheId .= implode('_', $_params);

            $this->cacheId = valid_cache($cacheId);
        }

        return $this->cacheId;
    }

    /**
     * Get the cache name
     *
     * @return String
     */
    public function getCacheName() {
        if($this->cacheName == null) {
            $module = $this->getRequest()->getModuleName();
            $controller = $this->getRequest()->getControllerName();
            $action = $this->getRequest()->getActionName();
            $this->cacheName = sprintf("%s_%s_%s", $module, $controller,$action);
            $this->cacheName = preg_replace('#[^a-zA-Z0-9_]#', '_', $this->cacheName);
        }

        return $this->cacheName;
    }


    /**
     * @return boolean true si il y a d'autres get que ceux ignorés
     */
    protected function hasOnlyIgnoredGets() {
        if($this->getStatus == null) {
            $this->_gets = $_GET;
            /// Clean get_array;
            unset($this->_gets['from']); /// Always unset from (it's only for tracking)
            unset($this->_gets['t']); /// And t for tracking
            /// remove all utm_
            foreach($this->_gets as $key => $value) {
                if(preg_match('/^utm_/', $key)) {
                    unset($this->_gets[$key]);
                }
            }
            $this->getStatus = (count($this->_gets)==0);
        }
        return $this->getStatus;
    }

    /**
     * Camelize a string
     *
     * @param String $lower_case_and_underscored_word
     * @return String
     */
    public function camelize($lower_case_and_underscored_word)
    {
        $string = $lower_case_and_underscored_word;
        $string = preg_replace('#/(.?)#e', "'::'.strtoupper('\\1')", $string);
        $string = preg_replace('/(^|_|-)+(.)/e', "strtoupper('\\2')", $string);

        return $string;
    }

    public function disableCache(){
        $this->disabled = true;
    }
}