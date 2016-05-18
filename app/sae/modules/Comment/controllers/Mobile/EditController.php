<?php

class Comment_Mobile_EditController extends Application_Controller_Mobile_Default {

    public function createAction() {

        if ($data = Zend_Json::decode($this->getRequest()->getRawBody())) {

            try {

                $comment = new Comment_Model_Comment();

                if (!$this->getSession()->getCustomerId())
                    throw new Exception($this->_("You need to be connected to create a post"));

                $comment->setText($data['text']);
                $comment->setCustomerId($this->getSession()->getCustomerId());
                $comment->setValueId($data['value_id']);

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