<?php

class Api_Service_ImageController extends Application_Controller_Mobile_Default {

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

        $params_null = array(
            "format"        => null,
            "device_width"  => null,
            "device_height" => null,
        );

        $params = $params + $params_raw + $params_null;

        try {

            $data = Siberian_Image::getForMobile($request->getBaseUrl(), $params["resource"], $params["format"], $params["device_width"], $params["device_height"]);

        } catch (Exception $e) {
            /** Default raw base64 image. */
            $data = img_to_base64(null);
        }

        $this->_sendRaw($data);
    }

}