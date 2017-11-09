<?php

class Event_View_Application_Edit_Custom_Form extends Admin_View_Default{

    protected $_events;

    public function getEvents(){

        if(!$this->_events) {
            $event = new Event_Model_Event_Custom();
            $this->_events = $event->findAll(array('agenda_id'=> $this->getCurrentEvent()->getId()));
        }
        return $this->_events;
    }
}