<?php

/**
 * Class Message_ApplicationController
 */
class Message_ApplicationController extends Application_Controller_Default
{
    /**
     *
     */
    public function indexAction()
    {
        $this->loadPartials();
    }

    /**
     *
     */
    public function loadmoreAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $messages = new Message_Model_Application_Message();
            $messages = $messages->findAllWithFiles($data["load_more_app_id"], $data["offset"]);
            $html = array("messages" => array(), "display_per_page" => Message_Model_Application_Message::DISPLAY_PER_PAGE);
            foreach ($messages["messages"] as $message) {
                if (is_null($message->getFirstname()) OR $message->getFirstname() == "") {
                    $message->setData("firstname", __("Unknown"));
                }
                $html["messages"][] = $this->getLayout()->addPartial(
                    'row_' . $message->getId(),
                    'admin_view_default',
                    'message/application/view/row.phtml')
                    ->setCurrentMessage($message)
                    ->toHtml();
            }
            $this->_sendJson($html);
        }
    }

    /**
     *
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            try {
                $application = $this->getApplication();

                $message = new Message_Model_Application_Message();
                $message
                    ->setAppId($application->getId())
                    ->setMessage($data['message'])
                    ->setData("firstname", __("Me"))
                    ->save();

                $html = array(
                    'success' => '1',
                    'success_message' => $this->_('Info successfully saved'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0,
                    'row_html' => $this->getLayout()
                        ->addPartial(
                            'row_' . $message->getId(),
                            'admin_view_default',
                            'message/application/view/row.phtml')
                        ->setCurrentMessage($message)
                        ->toHtml()
                );
            } catch (\Exception $e) {
                $html = array('message' => $e->getMessage());
            }

            $this->_sendJson($html);
        }
    }
}