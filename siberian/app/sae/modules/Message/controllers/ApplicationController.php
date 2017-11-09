<?php

class Message_ApplicationController extends Application_Controller_Default
{

    public function indexAction()
    {
        $this->loadPartials();
    }

    public function loadmoreAction() {
        if($data = $this->getRequest()->getPost()) {
            $messages = new Message_Model_Application_Message();
            $messages = $messages->findAllWithFiles($data["load_more_app_id"], $data["offset"]);
            $html = array("messages" => array(), "display_per_page" => Message_Model_Application_Message::DISPLAY_PER_PAGE);
            foreach($messages["messages"] as $message) {
                if(is_null($message->getFirstname()) OR $message->getFirstname() == "") {
                    $message->setData("firstname",$this->_("Unknown"));
                }
                $html["messages"][] = $this->getLayout()->addPartial('row_'.$message->getId(), 'admin_view_default', 'message/application/view/row.phtml')
                    ->setCurrentMessage($message)
                    ->toHtml()
                ;
            }
            $this->_sendHtml($html);
        }

    }

    public function saveAction() {
        if($data = $this->getRequest()->getPost()) {

            try {

                $message = new Message_Model_Application_Message();
                $message->setData($data)->setData("firstname", $this->_("Me"))->save();

                $html = array(
                    'success' => '1',
                    'success_message' => $this->_('Info successfully saved'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0,
                    'row_html' => $this->getLayout()->addPartial('row_'.$message->getId(), 'admin_view_default', 'message/application/view/row.phtml')
                        ->setCurrentMessage($message)
                        ->toHtml()
                );

            }
            catch(Exception $e) {
                $html = array('message' => $e->getMessage());
            }

            $this->_sendHtml($html);

        }
    }

    public function uploadAction() {
        if($app_id = $this->getRequest()->getParam("app_id")) {

            try {

                if (empty($_FILES) || empty($_FILES['file']['name'])) {
                    throw new Exception("No file has been sent");
                }

                $adapter = new Zend_File_Transfer_Adapter_Http();
                $adapter->setDestination(Core_Model_Directory::getTmpDirectory(true));

                if ($adapter->receive()) {

                    $file = $adapter->getFileInfo();

                    $data = array(
                        "success" => 1,
                        "name" => $file["file"]["name"],
                        "message" => $this->_("The file has been successfully uploaded")
                    );

                } else {
                    $messages = $adapter->getMessages();
                    if (!empty($messages)) {
                        $message = implode("\n", $messages);
                    } else {
                        $message = $this->_("An error occurred during the process. Please try again later.");
                    }

                    throw new Exception($message);
                }
            } catch (Exception $e) {
                $data = array(
                    "error" => 1,
                    "message" => $e->getMessage()
                );
            }

            $this->_sendHtml($data);

        }
    }
}