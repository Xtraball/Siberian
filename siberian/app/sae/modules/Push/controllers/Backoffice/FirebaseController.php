<?php

/**
 * Class Push_Backoffice_FirebaseController
 */
class Push_Backoffice_FirebaseController extends Backoffice_Controller_Default
{
    /**
     *
     */
    public function loadAction()
    {
        $credentials = (new Push_Model_Firebase())
            ->find('0', 'admin_id');

        if (!$credentials->getId()) {
            $credentials
                ->setAdminId(0)
                ->save();
        }

        $this->_sendJson([
            'success' => true,
            'firebase' => [
                'email' => $credentials->getEmail(),
                'password' => Push_Model_Firebase::$fakePassword,
                'projectNumber' => $credentials->getProjectNumber(),
            ],
            'projects' => $credentials->getRawProjects()
        ]);
    }

    /**
     *
     */
    public function credentialsAction()
    {
        $request = $this->getRequest();
        try {
            $params = $request->getBodyParams();

            if ($params['password'] === Push_Model_Firebase::$fakePassword) {
                throw new Siberian_Exception('#308-00: ' .__('Not saving unchanged password.'));
            }

            $firebase = new \Siberian\Firebase\Api();
            $firebase->login($params['email'], $params['password']);

            // Save settings in db
            $credentials = (new Push_Model_Firebase())
                ->find('0', 'admin_id');

            $projects = $firebase->getProjects();

            $credentials
                ->setAdminId(0)
                ->setEmail($params['email'])
                ->setCredentials($params['email'], $params['password'])
                ->setRawProjects($projects)
                ->save();

            $payload = [
                'success' => true,
                'message' => __('Successfully logged-in.'),
                'projects' => $projects,
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     *
     */
    public function projectAction()
    {
        $request = $this->getRequest();
        try {
            $params = $request->getBodyParams();

            // Save credentials in db
            $credentials = (new Push_Model_Firebase())
                ->find('0', 'admin_id');

            $credentials
                ->setProjectNumber($params['projectNumber'])
                ->save();

            $payload = [
                'success' => true,
                'message' => __('Successfully saved default project.'),
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
