<?php

class Zend_View_Helper_Cache {

	protected $_request;
	protected $_response;
	/**
	 * @var Zend_Cache_Frontend_Output
	 */
	protected $cache;
	protected $cacheIdView = '';
	protected $started = false;
	/**
	 * @var Zend_Log
	 */
	protected $_logger;
	
	protected $view;
	
	function setView($view){
		$this->view = $view;
		
		$this->_request = Zend_Controller_Front::getInstance()->getRequest();
		$this->_response = Zend_Controller_Front::getInstance()->getResponse();
		$this->_logger = Zend_Controller_Front::getInstance()->getParam("bootstrap")->getResource("Log");
		
		$this->cache = Zend_Controller_Front::getInstance()->getParam("bootstrap")->getResource("CacheManager")->getCache('output');
		
		$this->cacheIdView = $this->view->cacheObject['id'];
	}
	
	public function cache() {
		return $this;
	}
	
	public function start() {
		if(isset($this->view->cacheObject) && $this->_request->isDispatched() && !$this->_response->isRedirect()){
			//c'est cacheable
			$this->started = true;
			
			$cache = $this->cache->start($this->cacheIdView);
			
			if($cache) {
				$this->_response->setHeader('Zt-Cache', 'HIT');
			}else{
				$this->_response->setHeader('Zt-Cache', 'MISS');
			}
			
			return $cache;
		}else{
			$this->_response->setHeader('Zt-Cache', 'PASS');
			return false;
		}
	}
	
	public function end() {
		if($this->started){
			if($this->_request->isDispatched() && !$this->_response->isRedirect()){
				$this->cache->end(array($this->view->cacheObject['name']),$this->view->cacheObject['lifetime']);
			}else{
				echo (ob_get_clean()); //on a fait un ob_start avec le start du cache
				$this->_response->setHeader('Zt-Cache', 'PASS',true);
			}
		}
	}
	
}