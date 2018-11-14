<?php

/**
 * Class Message_Model_Application_Message
 */
class Message_Model_Application_Message extends Core_Model_Default
{

    const DISPLAY_PER_PAGE = 10;

    /**
     * Message_Model_Application_Message constructor.
     * @param array $params
     * @throws Zend_Exception
     */
    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->_db_table = 'Message_Model_Db_Table_Application_Message';
        return $this;
    }

    /**
     * @return $this|void
     */
    public function save()
    {
        parent::save();
        if ($this->getData("message_files")) {
            $new_files = [];
            foreach ($this->getData("message_files") as $key => $file) {
                if ($file AND $file != "") {
                    $new_files[] = $this->saveFile($file);
                }
            }
            $this->setMessageFiles($new_files);
        }
    }

    /**
     * @param string $message
     * @return $this
     * @throws Zend_Exception
     */
    public function setMessage($message)
    {
        $_filtered = \Siberian\Xss::sanitize($message);

        return $this->setData('message', $_filtered);
    }

    /**
     * @return string
     * @throws Zend_Exception
     */
    public function getMessage()
    {
        return \Siberian\Xss::sanitize($this->getData('message'));
    }

    /**
     * @param $file
     * @return null|string
     */
    public function saveFile($file)
    {
        $path = Core_Model_Directory::getTmpDirectory(true) . "/" . $file;
        $new_name = null;

        if (file_exists($path)) {
            $path_parts = pathinfo($path);
            $extension = $path_parts["extension"];
            $new_name = uniqid() . "." . $extension;
            $base_path = Core_Model_Directory::getBasePathTo("images/application/" . $this->getAppId() . "/messages/");
            if (!dir($base_path)) {
                mkdir($base_path, 0777, true);
            }
            rename($path, $base_path . $new_name);
            $message_file = new Message_Model_Application_File();
            $message_file->setMessageId($this->getId())
                ->setFile($new_name)
                ->save();
        }

        return $new_name;
    }

    /**
     * @param $app_id
     * @param $offset
     * @return mixed
     */
    public function findAllByAppId($app_id, $offset)
    {
        return $this->getTable()->findAllByAppId($app_id, $offset);
    }

    /**
     * @param $app_id
     * @param int $offset
     * @return mixed
     */
    public function findAllWithFiles($app_id, $offset = 0)
    {
        $messages = $this->findAllByAppId($app_id, $offset);
        $messages_list = [];
        foreach ($messages as $message) {
            $files = new Message_Model_Application_File();
            $file_list = [];
            foreach ($files->findAll(["message_id" => $message->getId()]) as $file) {
                $file_list[] = $file->getFile();
            }
            $message->setData("message_files", $file_list);
            $messages_list[] = $message;
        }
        $result["messages"] = $messages_list;
        $result["display_per_page"] = self::DISPLAY_PER_PAGE;
        return $result;
    }
}