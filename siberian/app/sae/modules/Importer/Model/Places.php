<?php

use Siberian\File;

/**
 * Class Importer_Model_Places
 */
class Importer_Model_Places extends Importer_Model_Importer_Abstract 
{
    /**
     * Importer_Model_Places constructor.
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
            if ($data['location']['latitude'] && $data['location']['longitude']) {

                $place = new Cms_Model_Application_Page();
                $newData = $this->_prepareDataFromFacebook($data, $appId);
                $optionValue = new Application_Model_Option_Value();
                $optionValue->find($data['value_id']);
                $place->edit_v2($optionValue, $newData);

                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @param $data
     * @param $appId
     * @return array
     */
    private function _prepareDataFromFacebook($data, $appId) 
    {
        // First we copy the cover if needed!
        if ($data['cover'] && $data['cover']['source']) {
            try {
                $folder = Core_Model_Directory::getTmpDirectory(true).'/';
                if (!is_dir($folder)) {
                    mkdir($folder, 0777, true);
                }
                $coverName = uniqid() . '.jpg';
                $cover = $folder . $coverName;
                File::putContents($cover, Siberian_Request::get($data['cover']['source']));
                $cover = $coverName;
            } catch (Exception $e) {
                $cover = null;
            }
        }

        $structuredData = [
            'value_id' => $data['value_id'],
            'page_id' => 'new',
            'cms_type' => 'places',
            'title' => $data['name'],
            'content' => $data['description'] ? $data['description'] : $data['about'],
            'metadata' => [
                'show_image' => 1,
                'show_titles' => 1
            ]
        ];

        if($data['location']['latitude'] && $data['location']['longitude']) {
            $structuredData['block'] = [
                'new' => [
                    'address' => [
                        'label' => $data['name'],
                        'address' => $data['location']['street'] . ' ' . $data['location']['zip']
                            . ' ' . $data['location']['city'],
                        'latitude' => $data['location']['latitude'],
                        'longitude' => $data['location']['longitude'],
                        'show_address' => 1,
                        'show_geolocation_button' => 1
                    ]
                ]
            ];
        }

        if ($cover) {
            $structuredData['places_file'] = $cover;
        }

        return $structuredData;
    }

}
