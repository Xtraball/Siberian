<?php

class Event_Mobile_ListController extends Application_Controller_Mobile_Default {

    public function findallAction() {

        if($value_id = $this->getRequest()->getParam('value_id')) {

            try {

                $option = $this->getCurrentOptionValue();

                $start_at = new Zend_Date();
                $end_at = new Zend_Date();
                $format = 'y-MM-dd HH:mm:ss';
                $offset = $this->getRequest()->getParam('offset', 0);
                $events = $option->getObject()->getEvents($offset);
                $data = array('collection' => array());
                foreach($events as $key => $event) {
                    $start_at->set($event->getStartAt(), $format);
                    $end_at->set($event->getEndAt(), $format);
                    $formatted_start_at = $start_at->toString($this->_("MM.dd.y"));

                    $subtitle2 = $this->_("Entrance: %s", $event->getStartTimeAt());
                    if($event->getLocationLabel()) {
                        $subtitle2 .= " | ".$this->_("Location: %s", $event->getLocationLabel());
                    }

                    if(!in_array($start_at->toString(Zend_Date::DATE_MEDIUM), $data["groups"])) {
                        $data["groups"][] = $start_at->toString(Zend_Date::DATE_MEDIUM);
                    }

                    $picture = $event->getPicture() ? $this->getRequest()->getBaseUrl().$event->getPicture() : null;
                    if($event->getType() == "facebook") $picture = $event->getPicture();
                    $data['collection'][] = array(
                        "id" => $key,
                        "picture" => $picture,
                        "group" => $start_at->toString(Zend_Date::DATE_MEDIUM),
                        "title" => $event->getName(),
                        "title2" => $formatted_start_at,
                        "name" => $event->getName(),
                        "subtitle" => $event->getSubtitle(),
                        "subtitle2" => $subtitle2,
                        "description" => $event->getDescription(),
                        "month_name_short" => $start_at->toString(Zend_Date::MONTH_NAME_SHORT),
                        "day" => $start_at->toString('dd'),
                        "weekday_name" => $start_at->toString(Zend_Date::WEEKDAY_NAME),
                        "start_time_at" => $event->getStartTimeAt(),
                        "location" => $event->getLocation(),
                        "url" => $this->getPath("event/mobile_view/index", array('value_id' => $option->getId(), "event_id" => $key))
                    );
                }

                $data['page_title'] = $option->getTabbarName();
                $data['displayed_per_page'] = Event_Model_Event::DISPLAYED_PER_PAGE;

            }
            catch(Exception $e) {
                $data = array('error' => 1, 'message' => $e->getMessage());
            }

            $this->_sendHtml($data);

        }

    }

}