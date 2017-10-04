<?php

class Media_Mobile_Gallery_Image_ViewController extends Application_Controller_Mobile_Default {

    public function findAction() {
        try {
            /** Do your stuff here. */
            $request = $this->getRequest();
            if (!($gallery_id = $request->getParam('gallery_id'))) {
                throw new Siberian_Exception(__('Missing gallery_id'));
            }

            $offset = $request->getParam('offset', 0);

            $image = (new Media_Model_Gallery_Image())
                ->find($gallery_id);

            if (!$image->getId() || ($image->getValueId() !== $this->getCurrentOptionValue()->getId())) {
                throw new Siberian_Exception(__('An error occurred while loading pictures. Please try later.'));
            }

            $images = $image
                ->setOffset($offset)
                ->getImages();

            $collection = [];
            foreach ($images as $key => $link) {
                $key = $key + $offset;

                $title = $link->getTitle();
                $description = $link->getDescription();

                $sub = $title;
                $sub = $sub . ($sub !== '') ? '<br />' . $description : $description;

                $collection[] = [
                    'offset' => (integer) $key,
                    'gallery_id' => (integer) $gallery_id,
                    'is_visible' => false,
                    'src' => stripos($link->getImage(), 'http') === false ?
                        $request->getBaseUrl().$link->getImage() : $link->getImage(),
                    'sub' => $sub,
                    'title' => $title,
                    'description' => $description,
                    'author' => $link->getAuthor()
                ];
            }

            $payload = [
                'collection' => $collection
            ];

            switch ($image->getTypeId()) {
                case 'flickr':
                    $payload['show_load_more'] = (boolean) $image->getTypeInstance()->showLoadMore();
                    break;
                case 'facebook':
                    $payload['show_load_more'] = $images[0] ? !is_null($images[0]->getOffset()) : false;
                    break;
                case 'custom':
                    // commented out as code can't work!
                    //$payload['show_load_more'] = count($payload['images']) > 0;
                    $payload['show_load_more'] = false;
                    break;
                default:
                    $payload['show_load_more'] = (($key - $offset) + 1) >
                        (Media_Model_Gallery_Image_Abstract::DISPLAYED_PER_PAGE - 1) ? true : false;
            }

        } catch (Exception $e) {
            $payload = [
                'error' => true,
                'message' => __('An unknown error occurred, please try again later.')
            ];
        }

        $this->_sendJson($payload);
    }

}
