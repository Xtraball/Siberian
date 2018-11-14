<?php

/**
 * Class Template_Backoffice_DesignController
 */
class Template_Backoffice_DesignController extends Backoffice_Controller_Default
{
    /**
     *
     */
    public function toggleactiveAction()
    {
        try {
            $request = $this->getRequest();
            $params = $request->getBodyParams();

            if (empty($params)) {
                throw new \Siberian\Exception(__('Missing params.'));
            }

            if (!isset($params['templateId']) || !isset($params['isActive'])) {
                throw new \Siberian\Exception(__('Missing design_id.'));
            }

            $templateDesign = (new Template_Model_Design())
                ->find($params['templateId']);

            if (!$templateDesign->getId()) {
                throw new \Siberian\Exception(__('The given template does not exists.'));
            }

            $templateDesign
                ->setIsActive($params['isActive'])
                ->save();
            
            $payload = [
                'success' => true,
                'message' => __('Success'),
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }
        
        $this->_sendJson($payload);
    }

}
