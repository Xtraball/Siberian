<?php

class Event_Application_EventController extends Application_Controller_Default
{

    /**
     * @var array
     */
    public $cache_triggers = array(
        "edit" => array(
            "tags" => array(
                "feature_paths_valueid_#VALUE_ID#",
                "assets_paths_valueid_#VALUE_ID#",
            ),
        ),
        "delete" => array(
            "tags" => array(
                "feature_paths_valueid_#VALUE_ID#",
                "assets_paths_valueid_#VALUE_ID#",
            ),
        ),
    );

    public function editAction()
    {
        if ($datas = $this->getRequest()->getPost()) {

            try {

                $application = $this->getApplication();

                // Test s'il y a un value_id
                if (empty($datas['agenda_id'])) {
                    throw new Exception(__('An error occurred while saving. Please try again later.'));
                }

                $event = new Event_Model_Event_Custom();
                $option_value = $this->getCurrentOptionValue();
                $data = array();

                if (!empty($datas['id'])) {
                    $event->find($datas['id']);
                    if ($event->getAgendaId() != $datas['agenda_id']) {
                        throw new Exception(__('An error occurred while saving. Please try again later.'));
                    }
                }

                if (empty($datas['end_at'])) {
                    $datas['end_at'] = null;
                    $datas['end_time_at'] = null;
                }

                if (!empty($datas['picture'])) {
                    $filename = $datas['picture'];
                    $img_src = tmp(true) . '/' . $filename;
                    if (file_exists($img_src)) {

                        $relative_path = $option_value->getImagePathTo();
                        $folder = Application_Model_Application::getBaseImagePath() . $relative_path;
                        $img_dst = $folder . '/' . $filename;

                        if (!is_dir($folder)) {
                            mkdir($folder, 0777, true);
                        }

                        if (!copy($img_src, $img_dst)) {
                            throw new exception(__("An error occurred while saving your picture. Please try againg later."));
                        }
                        $datas['picture'] = $relative_path . '/' . $filename;
                    } else {
                        unset($data['picture']);
                    }
                } else {
                    $datas['picture'] = null;
                }

                foreach (array("rsvp", "ticket_shop_url", "location_url") as $url_type) {
                    if (!empty($datas[$url_type]) && stripos($datas[$url_type], 'http') === false) {
                        $datas[$url_type] = 'http://' . $datas[$url_type];
                    }
                }

                if (!empty($datas["websites"]) && is_array($datas["websites"])) {
                    $websites = array();
                    $cpt = 0;
                    foreach ($datas["websites"] as $website) {
                        if (empty($website["label"]) || empty($website["url"])) {
                            continue;
                        }
                        if (stripos($website["url"], 'http') === false) {
                            $website["url"] = 'http://' . $website["url"];
                        }
                        $websites[++$cpt] = $website;
                    }
                    $datas["websites"] = json_encode($websites, JSON_UNESCAPED_UNICODE);
                } else {
                    $datas["websites"] = null;
                }

                $event->addData($datas)->save();

                /** Update touch date, then never expires (until next touch) */
                $option_value
                    ->touch()
                    ->expires(-1);

                $cache = Zend_Registry::get('cache');
                $cache->remove($option_value->getObject()->getCacheId());

                $html = array(
                    'success' => '1',
                    'success_message' => __("Event successfully saved"),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                );


            } catch (\Exception $e) {
                $html = array(
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));

        }

    }

    public function duplicateAction()
    {
        try {
            $request = $this->getRequest();
            $application = $this->getApplication();
            $data = $request->getParams();

            if (!isset($data['eventId']) || empty($data['eventId'])) {
                throw new Exception(p__('event', 'Missing event id for duplication!'));
            }

            $event = (new Event_Model_Event_Custom())->find($data['eventId']);
            if (!$event || !$event->getId()) {
                throw new Exception(p__('event', "This event doesn't exists!"));
            }

            $agenda = (new Event_Model_Event())->find($event->getAgendaId());
            if (!$agenda || !$agenda->getId()) {
                throw new Exception(p__('event', "This calendar doesn't exists!"));
            }

            if (!$application->valueIdBelongsTo($agenda->getValueId())) {
                throw new Exception(p__('event', "You do not own this calendar!"));
            }

            // Ok passed all checks!
            $copyEvent = $event->getData();
            $newEvent = new Event_Model_Event_Custom();

            unset($copyEvent['id']);
            unset($copyEvent['event_id']);
            $copyEvent['name'] = p__('event', 'Copy') . ' - ' . $copyEvent['name'];

            $newEvent->setData($copyEvent);
            $newEvent->save();

            $payload = [
                'success' => true,
                'message' => p__('event', 'Event is duplicated'),
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }
        $this->_sendJson($payload);
    }

    public function formAction()
    {

        if (!$this->getRequest()->getParam('agenda_id')) {
            throw new Exception(__("An error occurred while loading this event"));
        }

        $event = new Event_Model_Event_Custom();
        if ($id = $this->getRequest()->getParam('event_id')) {
            $event->find($id);
        }
        $html = $this->getLayout()->addPartial('event_custom', 'admin_view_default', 'event/application/edit/custom/edit/event.phtml')
            ->setEvent($event)
            ->setOptionValue($this->getCurrentOptionValue())
            ->setAgendaId($this->getRequest()->getParam('agenda_id'))
            ->toHtml();

        $this->getLayout()->setHtml($html);
    }

    public function validatecropAction()
    {
        if ($datas = $this->getRequest()->getPost()) {
            try {
                $uploader = new Core_Model_Lib_Uploader();
                $file = $uploader->savecrop($datas);
                $datas = array(
                    'success' => 1,
                    'file' => $file,
                    'message_success' => 'Enregistrement réussi',
                    'message_button' => 0,
                    'message_timeout' => 2,
                );
            } catch (Exception $e) {
                $datas = array(
                    'error' => 1,
                    'message' => $e->getMessage()
                );
            }
            $this->getLayout()->setHtml(Zend_Json::encode($datas));
        }
    }

    public function deleteAction()
    {

        if (!$this->getRequest()->getParam('id')) {
            throw new Exception(__("An error occurred while loading this event"));
        }

        $id = $this->getRequest()->getParam("id");
        $html = '';

        try {

            $event = new Event_Model_Event_Custom();
            $event->find($id);

            if ($event->getAgenda()->getValueId() != $this->getCurrentOptionValue()->getId()) {
                throw new Exception(__("An error occurred while deleting the event"));
            }

            $event->delete();

            $html = array(
                'event_id' => $id,
                'success' => 1,
                'success_message' => __('Event successfully deleted'),
                'message_timeout' => 2,
                'message_button' => 0,
                'message_loader' => 0
            );
            $cache = Zend_Registry::get('cache');
            $cache->remove('AGENDA_OVI_' . sha1($this->getCurrentOptionValue()->getId()));

        } catch (Exception $e) {
            $html = array(
                'message' => $e->getMessage(),
                'url' => '/event/admin/list'
            );
        }

        $this->getLayout()->setHtml(Zend_Json::encode($html));
    }
}
