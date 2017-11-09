<?php

class Event_MobileController extends Application_Controller_Mobile_Default {

    public function listAction() {

        if ($datas = $this->getRequest()->getPost()) {

            try {
                if (empty($datas['offset']))
                    throw new Exception($this->_('An error occurred while loading. Please try again later.'));

                $events = $this->getCurrentOptionValue()->getObject()->getEvents($datas['offset']);

                $events_list_html = $this->getLayout()->addPartial('events_list', 'admin_view_default', 'event/l1/view/list.phtml')
                    ->setCurrentOption($this->getCurrentOptionValue())
                    ->setEvents($events)
                    ->toHtml()
                ;

                $events_details_html = $this->getLayout()->addPartial('events_list', 'admin_view_default', 'event/l1/view/details/list.phtml')
                    ->setCurrentOption($this->getCurrentOptionValue())
                    ->setEvents($events)
                    ->toHtml()
                ;

                $html = array(
                    'events_list_html' => $events_list_html,
                    'events_details_html' => $events_details_html,
                );

            }
            catch(Exception $e) {
                $html = array('error' => 1, 'message' => $e->getMessage());
            }

            $this->_sendHtml($html);

        }

    }

}