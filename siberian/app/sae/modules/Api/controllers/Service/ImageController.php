<?php

class Api_Service_ImageController extends Core_Controller_Default {

    /**
     * This methods proxy images with device resolution
     * reduce them to the best ratio
     * then send the result as base64 for cache
     *
     * Backward it's using gregwar library which has internal cache structure.
     */
    public function proxyAction() {
        $request = $this->getRequest();
        $params = $request->getParams();
        $params_raw = Siberian_Json::decode($request->getRawBody());

        $params_null = [
            'format' => null,
            'device_width' => null,
            'device_height' => null,
        ];

        $params = $params + $params_raw + $params_null;

        try {
            $data = Siberian_Image::getForMobile(
                $request->getBaseUrl(),
                $params['resource'],
                $params['format'],
                $params['device_width'],
                $params['device_height']);

        } catch (Exception $e) {
            $data = img_to_base64(null);
        }

        $this->_sendRaw($data);
    }

    /**
     * Generates a thumbnail!
     */
    public function thumbnailAction () {
        $request = $this->getRequest();
        $resource = base64_decode($request->getParam('resource'));

        try {
            // Fallback for path/fullpath
            if (!file_exists($resource)) {
                $resource = Core_Model_Directory::getBasePathTo($resource);
                if (!file_exists($resource)) {
                    throw new Siberian_Exception(__('The given resource doesn\'t exists'));
                }
            }

            Siberian_Image::enableForceCache();

            $infos = Siberian_Image::getForMobile(
                $request->getBaseUrl(),
                $resource,
                'thumbnail',
                null,
                null,
                true
            );

            Siberian_Image::disableForceCache();

        } catch (Exception $e) {
            $data = img_to_base64(null);
            $infos = [
                'type' => 'png',
                'data' => $data
            ];
        }

        if (strpos($infos['data'], 'base64') !== false) {
            $contentType = 'image/jpeg';
            switch ($data['type']) {
                case 'png':
                        $contentType = 'image/png';
                    break;
                case 'gif':
                        $contentType = 'image/gif';
                    break;
            }
            header('Content-type: ' . $contentType);
            $this->_sendRaw(base64_decode(preg_replace('#^data:image/.*;base64,#', '', $infos['data'])));
        } else {
            header('Location: ' . $infos['data']);
            exit;
        }
    }

}