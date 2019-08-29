<?php

use Siberian\File;

/**
 * Class Importer_Model_Contact
 */
class Importer_Model_Contact extends Importer_Model_Importer_Abstract 
{
    /**
     * Importer_Model_Contact constructor.
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
        try{
            $newData = $this->_prepareDataFromFacebook($data, $appId);
            (new Contact_Model_Contact())
                ->addData($newData)
                ->save();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @param $data
     * @param $appId
     * @return array
     */
    private function _prepareDataFromFacebook($data, $appId) {
        //First we copy the cover if needed
        if ($data['cover'] && $data['cover']['source']) {
            try {
                $folder = Application_Model_Application::getBaseImagePath() . '/'
                    . $appId . '/features/contact/' . $data['value_id'] . '/';
                $folderCover = '/' . $appId . '/features/contact/' . $data['value_id'] . '/';
                if (!is_dir($folder)) {
                    mkdir($folder, 0777, true);
                }
                $coverName = uniqid().'.jpg';
                $cover = $folder . $coverName;
                File::putContents($cover, Siberian_Request::get($data['cover']['source']));
                $cover = $folderCover . $coverName;
            } catch (Exception $e) {
                $cover = null;
            }
        }

        $structuredData = [
            'value_id' => $data['value_id'],
            'name' => $data['name'],
            'description' => $data['description'] ? $data['description'] : $data['about'],
            'facebook' => $data['link'],
            'website' => $data['website'],
            'email' => $data['emails'] ? $data['emails'][0] : '',
            'civility' => '',
            'firstname' => '',
            'lastname' => '',
            'street' => $data['location']['street'] ? $data['location']['street'] : '',
            'postcode' => $data['location']['zip'] ? $data['location']['zip'] : '',
            'city' => $data['location']['city'] ? $data['location']['city'] : '',
            'country' => $data['location']['country'],
            'latitude' => $data['location']['latitude'],
            'longitude' => $data['location']['longitude'],
            'phone' => $data['phone']
        ];

        if ($cover) {
            $structuredData['cover'] = $cover;
        }

        return $structuredData;
    }
}
