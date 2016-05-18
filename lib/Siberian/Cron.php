<?php

/**
 * Class Siberian_Cron
 *
 * @author Zicar
 *
 * @todo refactoring for Siberian
 */

class Siberian_Cron {
	
	protected $_daoCron;
	
	protected $_booking;
	protected $_user;
	protected $_car;
	protected $_console;
	protected $_statistics;
	protected $_statistics2;
	
	/**
	 * @var Zend_Log
	 */
	protected $_logger;
	/**
	 * @var Zicar_Res_Loader_ServiceInterface
	 */
	protected $_service;
	
	public function __construct(){
		$this->_service = Zicar_Res_Loader_Service::getInstance();
		
		$this->_daoCron = new Zicar_Dao_Cron();
		$this->_booking = $this->_service->Booking;
		$this->_user = new Application_Model_User();
		$this->_car = $this->_service->Car;
		$this->_console = new Application_Model_Console();
		$this->_statistics = new Application_Model_Statistics();
		$this->_statistics2 = new Application_Model_Statistics2();
		$this->_mail = new Application_Model_Mail();
		$this->_payment = new Application_Model_Payment();
		
		$this->_logger = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getResource('Log');
	}
	
	public function find($id) {
		return $this->_daoCron->find($id)->current();
	}
	
	public function triggerId($id){
		$task = $this->_daoCron->find($id)->current();
		if($task != null) $this->execute($task);
		
		return $task->action;
	}
	
	public function triggerConsole($action){
		$this->executeConsoleAction($action);
		return $action;
	}
	
	public function triggerAll(){
		$hour =	(int)date("G"); // 0 through 23
		$day  =	(int)date("j"); // 1 through 31
		$wday = (int)date('w'); // 0(sunday) -> 6(saturday)
		
		$all = $this->_daoCron->getActive($day, $hour,$wday);
		
		$actions = array();
		foreach ($all as $task){
			$actions[] = $task->id.':'.$task->action;
			$this->execute($task);
		}
		
		return $actions;
	}
	
	public function findAll($arguments, $page = 1) {
	    $db = Zend_Db_Table_Abstract::getDefaultAdapter();
	    $select = $db->select()
	        ->from('cron')
	        ->order(array('state DESC','action ASC'))
        ;
	    
	    if (is_array($arguments)) {
	    	Application_Model_Helper::where('cron.id = ?', 'id', $arguments, $select);
	    	Application_Model_Helper::whereLike('cron.action LIKE ?', 'cron_action', $arguments, $select);
	    	Application_Model_Helper::whereLike('cron.description LIKE ?', 'description', $arguments, $select);
	    	Application_Model_Helper::where('cron.state = ?', 'state', $arguments, $select);
	    }
        
        
	    $pResults = Zend_Paginator::factory($select);
		$pResults->setCurrentPageNumber($page);

	    return $pResults;
	}
	
	public function edit($id,$data){
		$this->_daoCron->update($data, Zend_Db_Table::getDefaultAdapter()->quoteInto('id = ?', $id));
	}
	
	public function changeState($id,$state){
		$this->_daoCron->changeState($id, $state);
	}
	
	public function create($data){
		return $this->_daoCron->insert($data);
	}
	
	protected function executeConsoleAction($action){
		if(!is_callable(array($this->_console,$action)) ){
			echo 'console.'.$action.' : INCONNUE';
		}else{
			$this->_console->{$action}();
		}
	}
	
	/**
	 * @param Zend_Db_Table_Row_Abstract $task
	 */
	protected function execute($task){
		$task_action = explode('.',$task->action);	

		try{
			if( !isset($this->{'_'.$task_action[0]}) || !is_callable(array($this->{'_'.$task_action[0]},$task_action[1])) ){
				echo $task->action.' : INCONNUE';
			}else{
				$model = $this->{'_'.$task_action[0]};
				$model->{$task_action[1]}();
				$task->last_triggered = new Zend_Db_Expr('NOW()');
				$task->save();
			}
		}catch (Exception $e){
			$this->_logger->log($e, Zend_Log::ERR);
			$this->_service->Alert->logAlerte('cron.exc', $task->id,$task->action . ' : ' . $e->__toString());
		}
	}
	
}