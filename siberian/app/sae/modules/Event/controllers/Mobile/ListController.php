<?php

use Siberian_Google_Geocoding as Geocoding;

/**
 * Class Event_Mobile_ListController
 */
class Event_Mobile_ListController extends Application_Controller_Mobile_Default {

    public function findallAction() {

        if ($value_id = $this->getRequest()->getParam('value_id')) {

            try {

                $option = $this->getCurrentOptionValue();
                $offset = $this->getRequest()->getParam('offset', 0);
                $events = $option->getObject()->getEvents($offset);
                $data = ['collection' => []];

                foreach ($events as $key => $event) {
                    $formatted_start_at = datetime_to_format($event->getStartAt(), Zend_Date::DATE_MEDIUM);

                    $formatted_end_at = null;
                    if (!empty($event->getEndAt())) {
                        $formatted_end_at = datetime_to_format($event->getEndAt(), Zend_Date::DATE_MEDIUM);
                    }

                    $in_app_page_path = null;
                    if (is_numeric($event->getInAppValueId())) {
                        $option = new Application_Model_Option_Value();
                        $option->find($event->getInAppValueId());
                        if($option->getId() && $option->isActive() && $option->getAppId() == $this->getApplication()->getId()) {
                            $in_app_page_path = $option->getPath("index");
                        }
                    }

                    $subtitle2 = __("Entrance: %s", $event->getStartTimeAt());
                    if($event->getLocationLabel()) {
                        $subtitle2 .= " | ".__("Location: %s", $event->getLocationLabel());
                    }

                    if(!in_array($formatted_start_at, $data['groups'])) {
                        $data["groups"][] = $formatted_start_at;
                    }

                    $picture = $event->getPicture() ? $this->getRequest()->getBaseUrl().$event->getPicture() : null;
                    if ($event->getType() === 'facebook') {
                        $picture = $event->getPicture();
                    }

                    $picture_b64 = null;
                    if ($event->getPicture()) {
                        $picture = path($event->getPicture());
                        $picture_b64 = Siberian_Image::open($picture)->inline('jpg');
                    }

                    if ($event->getType() === 'facebook') {
                        $picture_b64 = Siberian_Image::open($event->getPicture())->inline('jpg');
                    }

                    $weekDayName = datetime_to_format($event->getStartAt(), Zend_Date::WEEKDAY_NAME);

                    $endTimeShort = null;
                    if (!empty($event->getEndAt()) && !empty($event->getEndTimeAt())) {
                        $endTimeShort = $event->getEndTimeAt();
                    }

                    // Geo
                    $application = $this->getApplication();
                    $gKey = $application->getGooglemapsKey();
                    $geocoded = false;
                    if (!empty($gKey)) {
                        $geocoded = Geocoding::getLatLng(['address' => $event->getAddress()], $gKey);
                        if (empty($geocoded[0]) || empty($geocoded[1])) {
                            $geocoded = false;
                        }
                    }

                    $data['collection'][] = [
                        "id" => $key,
                        "picture" => $picture_b64,
                        "group" => $formatted_start_at,
                        "title" => $event->getName(),
                        "title2" => $formatted_start_at,
                        "name" => $event->getName(),
                        "subtitle" => $event->getSubtitle(),
                        "subtitle2" => $subtitle2,
                        "description" => $event->getDescription(),
                        "month_name_short" => datetime_to_format($event->getStartAt(), Zend_Date::MONTH_NAME_SHORT),
                        "day" => datetime_to_format($event->getStartAt(), Zend_Date::DAY),
                        "weekday_name" => $weekDayName,
                        "start_time_at" => $event->getStartTimeAt(),
                        "location" => $event->getLocation(),
                        "url" => $this->getPath("event/mobile_view/index", ['value_id' => $option->getId(), "event_id" => $key]),
                        "embed_payload" => [
                            "event" => [
                                "id" => $event->getId(),
                                "title" => $event->getName(),
                                "description" => nl2br($event->getDescription()),
                                "geo" => $geocoded,
                                "address" => $event->getAddress(),
                                "weekday_name" => $weekDayName,
                                "start_at" => $formatted_start_at,
                                "end_at" => $formatted_end_at,
                                "ticket_shop_url" => $event->getTicketShopUrl(),
                                "rsvp" => $event->getRsvp(),
                                "websites" => $event->getWebsites(),
                                "in_app_page_path" => $in_app_page_path,
                                "social_sharing_active" => (boolean) $option->getSocialSharingIsActive()
                            ],
                            "cover" => [
                                "title" => $event->getName(),
                                "subtitle" => $event->getSubtitle(),
                                "title2" => $formatted_start_at,
                                "subtitle2" => [
                                    "time" => $event->getStartTimeAt(),
                                    "location" => [
                                        "label" => $event->getLocationLabel(),
                                        "url" => $event->getLocationUrl()
                                    ]
                                ],
                                "title3" => $formatted_end_at,
                                "subtitle3" => $endTimeShort,
                                "picture" => $picture_b64
                            ],
                            "page_title" => $event->getName()
                        ]
                    ];
                }

                $data['page_title'] = $option->getTabbarName();
                $data['displayed_per_page'] = Event_Model_Event::DISPLAYED_PER_PAGE;

            } catch (Exception $e) {
                $data = ['error' => 1, 'message' => $e->getMessage()];
            }

            $this->_sendJson($data);

        }

    }

}
