<?php

class Event_Model_Event_Custom extends Core_Model_Default {

    protected $_agenda;

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Event_Model_Db_Table_Event_Custom';
        return $this;
    }

    public function getAgenda() {

        if(!$this->_agenda) {
            $this->_agenda = new Event_Model_Event();
            $this->_agenda->find($this->getAgendaId());
        }

        return $this->_agenda;
    }

    public function getPictureUrl() {
        if($this->getData('picture')) {
            $path_picture = Application_Model_Application::getImagePath().$this->getData('picture');
            $base_path_picture = Application_Model_Application::getBaseImagePath().$this->getData('picture');
            if(file_exists($base_path_picture)) {
                return $path_picture;
            }
        }
        return null;
    }

    public function getWebsites() {
        $websites = array();
        if($this->getData("websites")) {
            try {
                $websites = Zend_Json::decode($this->getData("websites"));
            } catch(Exception $e) {
                $websites = array();
            }
        }
        return $websites;
    }

}
