<?php

/**
 * Class Push_AdminController
 */
class Push_AdminController extends Admin_Controller_Default
{
    /**
     *
     */
    public function globalAction()
    {
        $this->loadPartials();
    }

    /**
     * Send a global push message
     */
    public function sendAction()
    {
        $request = $this->getRequest();
        $values = $request->getPost();

        $form = new Push_Form_Global();
        if ($form->isValid($values)) {

            # Filter checked applications
            if (Siberian_Version::is('sae')) {
                $values['checked'] = [Application_Model_Application::getInstance()->getId()];
                $values['send_to_all'] = false;
            } else {
                $values['checked'] = explode(',', $values['checked']);
            }

            if (!empty($values['cover'])) {
                $picture = Siberian_Feature::moveAsset(
                    sprintf("%s/%s", Core_Model_Directory::getTmpDirectory(), $values['cover']));

                $values['cover'] = $picture;
            } else {
                $values['cover'] = null;
            }

            $values['base_url'] = $this->getRequest()->getBaseUrl();

            $push_global = new Push_Model_Message_Global();
            $result = $push_global->createInstance($values);

            $data = [
                'success' => true,
                'message' => ($result) ? __('Push message is sent.') :
                    __('No message sent, there is no available applications.'),
            ];
        } else {
            /** Do whatever you need when form is not valid */
            $data = [
                'error' => true,
                'message' => $form->getTextErrors(),
                'errors' => $form->getTextErrors(true),
            ];
        }

        $this->_sendJson($data);
    }
}