<?php

class Event_Mobile_ListController extends Application_Controller_Mobile_Default {

    public function findallAction() {

        if($value_id = $this->getRequest()->getParam('value_id')) {

            try {



                $option = $this->getCurrentOptionValue();
                /**if($option) {
                    $folder = new Folder_Model_Folder();
                    $payload = $option->getObject()->getEmbedPayload($option);

                    $this->_sendJson($payload, true);
                }*/

                $start_at = new Zend_Date();
                $end_at = new Zend_Date();
                $format = 'y-MM-dd HH:mm:ss';
                $offset = $this->getRequest()->getParam('offset', 0);
                $events = $option->getObject()->getEvents($offset);
                $data = array('collection' => array());
                foreach($events as $key => $event) {
                    $start_at->set($event->getStartAt(), $format);
                    $end_at->set($event->getEndAt(), $format);
                    $formatted_start_at = $start_at->toString(__("MM.dd.y"));
                    $formatted_end_at = $end_at->toString(__("MM.dd.y"));
                    $in_app_page_path = null;
                    if(is_numeric($event->getInAppValueId())) {
                        $option = new Application_Model_Option_Value();
                        $option->find($event->getInAppValueId());
                        if($option->getId() AND $option->isActive() AND $option->getAppId() == $this->getApplication()->getId()) {
                            $in_app_page_path = $option->getPath("index");
                        }
                    }

                    $subtitle2 = __("Entrance: %s", $event->getStartTimeAt());
                    if($event->getLocationLabel()) {
                        $subtitle2 .= " | ".__("Location: %s", $event->getLocationLabel());
                    }

                    if(!in_array($start_at->toString(Zend_Date::DATE_MEDIUM), $data["groups"])) {
                        $data["groups"][] = $start_at->toString(Zend_Date::DATE_MEDIUM);
                    }

                    $picture = $event->getPicture() ? $this->getRequest()->getBaseUrl().$event->getPicture() : null;

                    if($event->getType() == "facebook") {
                        $picture = $event->getPicture();
                    }

                    $picture_b64 = null;
                    if($event->getPicture()) {
                        $picture = Core_Model_Directory::getBasePathTo($event->getPicture());
                        $picture_b64 = Siberian_Image::open($picture)->inline("png");
                    }

                    if($event->getType() == "facebook") {
                        $picture_b64 = Siberian_Image::open($event->getPicture())->inline("png");
                    }

                    $data['collection'][] = array(
                        "id" => $key,
                        "picture" => $picture_b64,
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
                        "url" => $this->getPath("event/mobile_view/index", array('value_id' => $option->getId(), "event_id" => $key)),
                        "embed_payload" => array(
                            "event" => array(
                                "id"                        => $event->getId(),
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
                            ),
                            "cover" => array(
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
                                "subtitle3" => $end_at->toString(__("hh:mm a")),
                                "picture" => $picture_b64
                            ),
                            "page_title" => $event->getName()
                        )
                    );
                }

                $data['page_title'] = $option->getTabbarName();
                $data['displayed_per_page'] = Event_Model_Event::DISPLAYED_PER_PAGE;

            } catch(Exception $e) {
                $data = array('error' => 1, 'message' => $e->getMessage());
            }

            $this->_sendJson($data);

        }

    }

}