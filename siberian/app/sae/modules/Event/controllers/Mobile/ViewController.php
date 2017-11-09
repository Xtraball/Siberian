<?php

class Event_Mobile_ViewController extends Application_Controller_Mobile_Default {

    public function findAction() {

        if($value_id = $this->getRequest()->getParam('value_id')) {

            $event_id = $this->getRequest()->getParam('event_id');

            if(is_null($event_id)) {
                return $this;
            }

            try {

                $option = $this->getCurrentOptionValue();

                $start_at = new Zend_Date();
                $end_at = new Zend_Date();
                $format = 'y-MM-dd HH:mm:ss';
                $events = $option->getObject()->getEvents(null,true);

                if(!empty($events[$event_id])) {

                    $event = $events[$event_id];
                    $data = array('event' => array());

                    $start_at->set($event->getStartAt(), $format);
                    $end_at->set($event->getEndAt(), $format);
                    $formatted_start_at = $start_at->toString($this->_("MM.dd.y"));
                    $formatted_end_at = $end_at->toString($this->_("MM.dd.y"));
                    $in_app_page_path = null;
                    if(is_numeric($event->getInAppValueId())) {
                        $option = new Application_Model_Option_Value();
                        $option->find($event->getInAppValueId());
                        if($option->getId() AND $option->isActive() AND $option->getAppId() == $this->getApplication()->getId()) {
                            $in_app_page_path = $option->getPath("index");
                        }
                    }

                    $picture = $event->getPicture() ? $this->getRequest()->getBaseUrl().$event->getPicture() : null;
                    if($event->getType() == "facebook") $picture = $event->getPicture();

                    $data['event'] = array(
                        "id"                        => $event_id,
                        "title"                     => $event->getName(),
                        "description"               => nl2br($event->getDescription()),
                        "address"                   => $event->getAddress(),
                        "weekday_name"              => $start_at->toString(Zend_Date::WEEKDAY_NAME),
                        "start_at"                  => $formatted_start_at,
                        "end_at"                    => $formatted_end_at,
                        "ticket_shop_url"           => $event->getTicketShopUrl(),
                        "rsvp"                      => $event->getRsvp(),
                        "websites"                  => $event->getWebsites(),
                        "in_app_page_path"          => $in_app_page_path,
                        "social_sharing_active"     => (boolean) $option->getSocialSharingIsActive()
                    );

                    $data["cover"] = array(
                        "title" => $event->getName(),
                        "subtitle" => $event->getSubtitle(),
                        "title2" => $formatted_start_at,
                        "subtitle2" => array(
                            "time" => $event->getStartTimeAt(),
                            "location" => array(
                                "label" => $event->getLocationLabel(),
                                "url" => $event->getLocationUrl()
                            )
                        ),
                        "title3" => $formatted_end_at,
                        "subtitle3" => $end_at->toString($this->_("hh:mm a")),
                        "picture" => $picture
                    );

                    $data['page_title'] = $event->getName();

                } else {
                    throw new Siberian_Exception("Unable to find this event.", 410);
                }

            }
            catch(Exception $e) {

                $data = array(
                    "error"     => true,
                    "message"   => $e->getMessage()
                );

                if($e->getCode() === 410) {
                    $data["gone"] = true;
                }
            }

            $this->_sendJson($data);

        }

    }

}