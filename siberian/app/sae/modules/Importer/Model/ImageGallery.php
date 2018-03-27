<?php

/**
 * Class Importer_Model_ImageGallery
 */
class Importer_Model_ImageGallery extends Importer_Model_Importer_Abstract 
{

    /**
     * Importer_Model_ImageGallery constructor.
     * @param array $params
     */
    public function __construct($params = []) 
    {
        parent::__construct($params);

    }

    /**
     * @param $data
     * @param null $appId
     * @return bool
     */
    public function importFromFacebook($data, $appId = null) 
    {
        try {
            $valueId = $data['value_id'];
            $pageId = $data['page_id'];
            foreach ($data['albums'] as $tmp) {
                if ($tmp['name']) {
                    $gal = new Media_Model_Gallery_Image();
                    $gal->addData(
                        [
                            'value_id' => $valueId,
                            'type_id' => 'facebook',
                            'name' => $pageId,
                            'label' => $tmp['name']
                        ]
                    )->save();

                    $fb = new Media_Model_Gallery_Image_Facebook();
                    $fb->addData(
                        [
                            'name' => $tmp['name'],
                            'album_id' => $tmp['id'],
                            'gallery_id' => $gal->getId()
                        ]
                    )->save();
                }
            }

            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
