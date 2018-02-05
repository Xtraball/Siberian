<?php

/**
 * Class Folder2_Mobile_ListController
 */
class Folder2_Mobile_ListController extends Application_Controller_Mobile_Default {

    /**
     * find folder
     */
    public function findallAction() {
        try {
            $request = $this->getRequest();
            if ($valueId = $request->getParam('value_id')) {
                $option = $this->getCurrentOptionValue();
                if ($option) {
                    $option->setRequest($request);
                    $payload = $option->getObject()->getEmbedPayload($option);
                } else {
                    throw new Siberian_Exception(__('Unable to find option.'));
                }
            } else {
                throw new Siberian_Exception(__('Missing parameter value_id.'));
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
