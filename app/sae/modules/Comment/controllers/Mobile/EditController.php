<?php

class Comment_Mobile_EditController extends Application_Controller_Mobile_Default {

    /**
     * @var array
     */
    public $cache_triggers = array(
        "create" => array(
            "tags" => array(
                "feature_paths_valueid_#VALUE_ID#",
                "assets_paths_valueid_#VALUE_ID#",
            ),
        ),
    );

    public function createAction() {

        if ($data = Zend_Json::decode($this->getRequest()->getRawBody())) {

            try {

                $comment = new Comment_Model_Comment();

                if (!$this->getSession()->getCustomerId())
                    throw new Exception($this->_("You need to be connected to create a post"));

                $comment->setText($data['text']);
                $comment->setCustomerId($this->getSession()->getCustomerId());
                $comment->setValueId($this->getRequest()->getParam("value_id", $data['value_id']));

                $position = $data['position'];
                if ($position) {
                    $comment->setLatitude($position['latitude']);
                    $comment->setLongitude($position['longitude']);
                }

                $image = $data['image'];
                if ($image) {
                    $url = $this->_saveImageContent($image);
                    $comment->setImage($url);
                }

                $comment->save();

                $message = $this->_('Your post was successfully added');
                $html = array('success' => 1, 'message' => $message);

            } catch (Exception $e) {
                $html = array('error' => 1, 'message' => $e->getMessage());
            }

            $this->_sendHtml($html);
        }
    }

    /**
     * Create fanwall post Siberian 5.0
     */
    public function createv2Action() {

        try {
            $request = $this->getRequest();
            $session = $this->getSession();
            if($params = Siberian_Json::decode($request->getRawBody())) {

                if (!$session->getCustomerId()) {
                    throw new Siberian_Exception(__("You must be connected to create a post!"));
                }

                $comment = new Comment_Model_Comment();
                $comment
                    ->setText($params["text"])
                    ->setCustomerId($session->getCustomerId())
                    ->setValueId($params["value_id"]);

                $position = $params["position"];
                if ($position) {
                    $comment
                        ->setLatitude($position['latitude'])
                        ->setLongitude($position['longitude']);
                }

                $image = $params["image"];
                if ($image) {
                    $url = $this->_saveImageContent($image);
                    $comment->setImage($url);
                }

                $comment->save();

                $payload = array(
                    "success" => true,
                    "message" => __("Your post was successfully added")
                );

            } else {
                throw new Siberian_Exception(__("Missing parameters, value_id or message."));
            }

        } catch(Exception $e) {

            $payload = array(
                "error" => true,
                "message" => $e->getMessage()
            );
        }

        $this->_sendJson($payload);
    }

    // Reference parameter
    protected function _saveImageContent($image) {

        if (!preg_match("@^data:image/([^;]+);@", $image, $matches)) {
            throw new Exception($this->_("Unrecognized image format"));
        }

        $extension = $matches[1];

        $fileName = uniqid() . '.' . $extension;
        $relativePath = $this->getCurrentOptionValue()->getImagePathTo();
        $fullPath = Application_Model_Application::getBaseImagePath() . $relativePath;
        if (!is_dir($fullPath)) mkdir($fullPath, 0777, true);
        $filePath = $fullPath . '/' . $fileName;

        $contents = file_get_contents($image);
        if ($contents === FALSE) {
            throw new Exception($this->_("No uploaded image"));
        }

        $res = @file_put_contents($filePath, $contents);
        if ($res === FALSE) throw new Exception('Unable to save image');

        return $relativePath . '/' . $fileName;
    }

}