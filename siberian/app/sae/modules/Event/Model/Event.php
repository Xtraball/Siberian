<?php

class Event_Model_Event extends Core_Model_Default {

    const DISPLAYED_PER_PAGE = 10;

    protected $_list = array();
    protected $_tmp_list = array();

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Event_Model_Db_Table_Event';
        return $this;
    }

    /**
     * @return array
     */
    public function getInappStates($value_id) {

        $in_app_states = array(
            array(
                "state" => "event-list",
                "offline" => false,
                "params" => array(
                    "value_id" => $value_id,
                ),
            ),
        );

        return $in_app_states;
    }

    /**
     * @todo to be cached at some point.
     *
     * @param $option_value
     * @return array
     */
    public function getFeaturePaths($option_value) {
        if(!$this->isCacheable()) {
            return array();
        }

        $value_id = $option_value->getId();
        $cache_id = "feature_paths_valueid_{$value_id}";
        if(!$result = $this->cache->load($cache_id)) {

            $action_view = $this->getActionView();

            $paths = array();

            $params = array(
                'value_id' => $option_value->getId(),
                'offset' => 0
            );
            $paths[] = $option_value->getPath("findall", $params, false);

            if($uri = $option_value->getMobileViewUri($action_view)) {

                $events = $this->getEvents();
                foreach ($events as $key => $event) {
                    $params = array(
                        "value_id" => $option_value->getId(),
                        "event_id" => $key
                    );
                    $paths[] = $option_value->getPath($uri, $params, false);
                }

            }

            $this->cache->save($paths, $cache_id, array(
                "feature_paths",
                "feature_paths_valueid_{$value_id}"
            ));
        } else {
            $paths = $result;
        }

        return $paths;

    }

    public function getEvents($offset = 0, $all_event=false) {

            $events = $this->findAll(array('value_id' => $this->getValueId()));
            $this->_list = array();
            foreach ($events as $event) {
                if($event->getEventType() == 'ical') {
                    $this->_parseIcalAgenda($event->getData('url'));
                } elseif($event->getEventType() == 'fb'){
                    $this->_parseFBAgenda($event->getData('url'));
                } else {
                    $this->_parseCustomAgenda($event->getId());
                }
            }
            usort($this->_tmp_list, array($this, '_sortByDate'));

            $this->_list = array();
            foreach($this->_tmp_list as $event) {
                if(is_array($event)) $event = new Core_Model_Default($event);
                $this->_list[] = $event;
            }

        if($all_event) {
            return $this->_list;
        } else {
            return array_slice($this->_list, $offset, self::DISPLAYED_PER_PAGE, true);
        }
    }

    public function getCacheId() {
        return 'AGENDA_OVI_' . sha1($this->getValueId() . Core_Model_Language::getCurrentLanguage());
    }

    public function copyTo($option) {

        if($this->getEventType() == 'cstm') {
            $custom_event = new Event_Model_Event_Custom();
            $custom_events = $custom_event->findAll(array('agenda_id' => $this->getId()));

            $this->setId(null)
                ->setValueId($option->getId())
                ->save()
            ;

            foreach($custom_events as $custom_event) {

                $custom_event->setId(null)
                    ->setAgendaId($this->getId())
                ;

                if($image_url = $custom_event->getPictureUrl()) {
                    $file = pathinfo($image_url);
                    $filename = $file['basename'];

                    $relativePath = $option->getImagePathTo();
                    $folder = Core_Model_Directory::getBasePathTo(Application_Model_Application::PATH_IMAGE.'/'.$relativePath);

                    if(!is_dir($folder)) {
                        mkdir($folder, 0777, true);
                    }

                    $img_src = Core_Model_Directory::getBasePathTo($image_url);
                    $img_dst = $folder.'/'.$filename;

                    if(copy($img_src, $img_dst)) {
                        $custom_event->setPicture($relativePath.'/'.$filename);
                    }
                }

                $custom_event->save();
            }

        } else {
            $this->setId(null)
                ->setValueId($option->getId())
                ->save()
            ;
        }

        return $this;

    }

    protected  function _parseIcalAgenda($url) {

        $content = file_get_contents($url);
        if(!$content) return $this;

        $ical = new Ical_Reader($content);
        $timezone = $ical->timezone();
        foreach ($ical->events() as $key => $event){
            if(strtotime($event['DTSTART']) > strtotime(date("Y-m-d H:i:s", time()))){
                $created_at = null;
                if(!empty($event['CREATED'])) {
                    $timestamp = $ical->iCalDateToUnixTimestamp($event['CREATED']);
                    $created_at = new Zend_Date($timestamp);
                    $created_at = $created_at->toString('y-MM-dd HH:mm:ss');
                }
                $start_at = new Zend_Date($event['DTSTART'],Zend_Date::ISO_8601);
                if(!empty($timezone)){
                    $start_at = $start_at->setTimezone($timezone);
                }
                $start_time_at = $start_at->toString('HH:mm');
                $start_at = $start_at->toString('y-MM-dd HH:mm:ss');
                $end_at = new Zend_Date($event['DTEND'],Zend_Date::ISO_8601);
                if(!empty($timezone)){
                    $end_at = $end_at->setTimezone($timezone);
                }
                $end_at = $end_at->toString('y-MM-dd HH:mm:ss');
                $this->_tmp_list[] = array(
                    "id"            => $key,
                    "name"          => $event['SUMMARY'],
                    "start_at"      => $start_at,
                    "start_time_at" => $start_time_at,
                    "end_at"        => $end_at,
                    "description"   => preg_replace('/\v+|\\\[rn]/','<br/>', $event['DESCRIPTION']),
                    "location"      => isset($event['LOCATION']) ? $event['LOCATION'] : '',
                    "rsvp"          => '',
                    "picture"       => $this->_getNoImage(),
                    "created_at"    => $created_at,
                    "updated_at"    => null
                );
            }
        }
        return $this;
    }

    protected  function _parseFBAgenda($username){

        $access_token = Core_Model_Lib_Facebook::getAppToken();

        $date = new Zend_Date();

        $url = "https://graph.facebook.com/v2.7/$username/events?since=".$date->toString("YYYY-MM-dd")."&access_token=$access_token";

        $response = file_get_contents($url);

        if(!$response) {
            return $this;
        }

        $events = Siberian_Json::decode($response);

        if (!empty($events) && !empty($events['data'])){
            foreach ($events['data'] as $key => $event){
                $event_datas = file_get_contents("https://graph.facebook.com/v2.7/{$event['id']}?access_token=$access_token");

                if(!$event_datas) continue;
                $description = '';
                if(!$event_datas) continue;

                $event_datas = Siberian_Json::decode($event_datas);

                $updated_at = date_create($event_datas['updated_time'])->format('Y-m-d H:i:s');

                if(!empty($event_datas['venue'])) {
                    if(!empty($event_datas["venue"]["name"])) {
                        $address = $event_datas["venue"]["name"];
                    } else {
                        $address = array();
                        foreach(array("street", "zip", "city") as $address_element) {
                            if(!empty($event_datas['venue'][$address_element])) {
                                $address[] = $event_datas['venue'][$address_element];
                            }
                        }
                        $address = implode(", ", $address);
                    }
                }

                $start_at = null;
                $start_time_at = null;
                if(!empty($event['start_time'])) {
                    $start_at = new Zend_Date($event['start_time'], Zend_Date::ISO_8601);
                    $start_time_at = $start_at->toString('HH:mm');
                    $start_at = $start_at->toString('y-MM-dd HH:mm:ss');
                }

                $this->_tmp_list[] = array(
                    "id"            => $key,
                    "name"          => $event['name'],
                    "start_at"      => $start_at,
                    "start_time_at" => $start_time_at,
                    "end_at"        => date_create(isset($event['end_time']) ? $event['end_time'] : "")->format('Y-m-d H:i:s'),
                    "description"   => !empty($event_datas['description']) ? $event_datas['description'] : null,
                    "location"      => $address,
                    "rsvp"          => '',
                    "picture"       => 'https://graph.facebook.com/'.$event['id'].'/picture?type=large',
                    "created_at"    => null,
                    "type"          => "facebook",
                    "updated_at"    => $updated_at

                );
//                }
            }
        }

        $this->_tmp_list = array_reverse($this->_tmp_list);

        return $this;

    }


    protected function _parseCustomAgenda($custom_agenda_id){
        $event = new Event_Model_Event_Custom();
        $custom_events = $event->findAll(array('agenda_id'=> $custom_agenda_id));
        foreach ($custom_events as $custom_event) {
            if(strtotime($custom_event->getEndAt()) > strtotime(date("Y-m-d H:i:s", time()))) {
                $image = $custom_event->getPictureUrl();
                if(!$image) {
                    $image = $this->_getNoImage();;
                }
                $custom_event->setPicture($image);
                $this->_tmp_list[] = $custom_event;
            }
        }

    }

    protected function _getNoImage() {
        return Application_Model_Application::getImagePath().'/placeholder/no-image-event.png';
    }

    protected function _sortByDate($a, $b) {

        if(is_array($a)) {
            $a_start_at = $a["start_at"];
        } else {
            $a_start_at = $a->getStartAt();
        }

        if(is_array($b)) {
            $b_start_at = $b["start_at"];
        } else {
            $b_start_at = $b->getStartAt();
        }

        return strtotime($a_start_at) > strtotime($b_start_at);
    }

    protected function msort($array, $key, $sort_flags = SORT_REGULAR) {
        if (is_array($array) && count($array) > 0) {
            if (!empty($key)) {
                $mapping = array();
                foreach ($array as $k => $v) {
                    $sort_key = '';
                    if (!is_array($key)) {
                        $sort_key = $v[$key];
                    } else {
                        // @TODO This should be fixed, now it will be sorted as string
                        foreach ($key as $key_key) {
                            $sort_key .= $v[$key_key];
                        }
                        $sort_flags = SORT_STRING;
                    }
                    $mapping[$k] = $sort_key;
                }
                asort($mapping, $sort_flags);
                $sorted = array();
                foreach ($mapping as $k => $v) {
                    $sorted[] = $array[$k];
                }
                return $sorted;
            }
        }
        return $array;
    }

    public function createDummyContents($option_value, $design, $category) {

        $dummy_content_xml = $this->_getDummyXml($design, $category);

        foreach ($dummy_content_xml->events->event as $event) {
            $this->unsData();

            $this->setValueId($option_value->getId())
                ->addData((array) $event->content)
                ->save()
            ;

            if($event->custom_contents) {
                foreach ($event->custom_contents->custom_content as $custom_content) {
                    $custom = new Event_Model_Event_Custom();
                    $custom->addData((array) $custom_content)
                        ->setAgendaId($this->getId())
                        ->save()
                    ;
                }
            }
        }
    }
}
