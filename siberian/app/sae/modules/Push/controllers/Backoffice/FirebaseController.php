<?php

/**
 * Class Push_Backoffice_FirebaseController
 */
class Push_Backoffice_FirebaseController extends Backoffice_Controller_Default
{
    /**
     * @throws Zend_Exception
     */
    public function loadAction()
    {
        $credentials = (new Push_Model_Firebase())
            ->find('0', 'admin_id');

        if (!$credentials->getId()) {
            $credentials
                ->setAdminId(0)
                ->save();
        }

        $senderID = $credentials->getSenderId();
        $serverKey = $credentials->getServerKey();
        if (__getConfig('is_demo')) {
            // Demo version
            $senderID = 'demo';
            $serverKey = 'demo';
        }

        $this->_sendJson([
            'success' => true,
            'firebase' => [
                'senderID' => $senderID,
                'serverKey' => $serverKey,
                'googleService' => $credentials->getGoogleService(),
            ]
        ]);
    }

    /**
     *
     */
    public function projectAction()
    {
        $request = $this->getRequest();
        try {
            if(__getConfig('is_demo')) {
                throw new \Siberian\Exception("This is a demo version, you can't alter this configuration.");
            }

            $params = $request->getBodyParams();

            // Save credentials in db
            $credentials = (new Push_Model_Firebase())
                ->find('0', 'admin_id');

            $credentials
                ->setSenderId($params['senderID'])
                ->setServerKey($params['serverKey'])
                ->save();

            $payload = [
                'success' => true,
                'message' => __('Successfully saved default project.'),
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     * Reads the google-service file!
     */
    public function serviceAction ()
    {
        try {
            // Demo version
            if(__getConfig('is_demo')) {
                throw new \Siberian\Exception("This is a demo version, you can't alter this configuration.");
            }

            if (empty($_FILES) || empty($_FILES['file']['name'])) {
                throw new \Siberian\Exception(__("No file has been sent"));
            }

            $adapter = new Zend_File_Transfer_Adapter_Http();
            $adapter->setDestination(Core_Model_Directory::getTmpDirectory(true));

            if ($adapter->receive()) {
                $file = $adapter->getFileInfo();
                $content = json_decode(file_get_contents($file['file']['tmp_name']), true);

                // Save credentials in db
                $credentials = (new Push_Model_Firebase())
                    ->find('0', 'admin_id');

                // Formatting google-services.json
                $content = Push_Model_Firebase::formatGoogleServices($content);

                $credentials
                    ->setGoogleService(json_encode($content))
                    ->save();

                $payload = [
                    'success' => true,
                    'message' => __('Google Service file successfully saved!'),
                    'content' => $content
                ];
            } else {
                $messages = $adapter->getMessages();
                if (!empty($messages)) {
                    $message = implode("\n", $messages);
                } else {
                    $message = __("An error occurred during the process. Please try again later.");
                }

                throw new \Siberian\Exception($message);
            }
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }
}
