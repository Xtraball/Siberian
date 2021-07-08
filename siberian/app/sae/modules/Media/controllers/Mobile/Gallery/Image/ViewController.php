<?php

class Media_Mobile_Gallery_Image_ViewController extends Application_Controller_Mobile_Default {

    public function findAction() {
        try {
            $request = $this->getRequest();
            if (!($gallery_id = $request->getParam('gallery_id'))) {
                throw new Siberian_Exception(__('Missing gallery_id'));
            }

            $offset = $request->getParam('offset', 0);

            $image = (new Media_Model_Gallery_Image())
                ->find($gallery_id);

            if (!$image->getId()) {
                throw new Siberian_Exception(__('An error occurred while loading pictures. Please try later.'));
            }

            $images = $image
                ->setOffset($offset)
                ->getImages();

            $payload = [];

            $collection = [];
            $key = 0;
            foreach ($images as $key => $link) {
                $key = $key + $offset;

                $title = $link->getTitle();
                $description = $link->getDescription();

                $sub = $title;
                $sub = $sub . ($sub !== '') ? '<br />' . $description : $description;

                $loopPicture = [
                    'id' => uniqid('img_', true),
                    'offset' => (integer) $key,
                    'gallery_id' => (integer) $gallery_id,
                    'is_visible' => false,
                    'src' => stripos($link->getImage(), 'http') === false ?
                        $request->getBaseUrl() . $link->getImage() : $link->getImage(),
                    'sub' => $sub,
                    'title' => $title,
                    'description' => $description,
                    'author' => $link->getAuthor()
                ];

                if (stripos($link->getImage(), 'http') === false) {
                    $thumb = __url($request->getBaseUrl() . 'api/service_image/thumbnail', [
                        'resource' => urlencode(base64_encode($link->getImage()))
                    ]);
                    $loopPicture['thumb'] = $thumb;
                }

                $collection[] = $loopPicture;

            }

            $payload['collection'] = $collection;

            switch ($image->getTypeId()) {
                case 'flickr':
                    $payload['show_load_more'] = (boolean) $image->getTypeInstance()->showLoadMore();
                    break;
                default:
                    $payload['show_load_more'] = ((($key - $offset) + 1) >
                        (Media_Model_Gallery_Image_Abstract::DISPLAYED_PER_PAGE - 1));
            }
        } catch (Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }

}
