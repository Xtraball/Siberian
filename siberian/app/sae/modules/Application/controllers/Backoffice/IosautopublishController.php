<?php

/**
 * Class Application_Backoffice_IosautopublishController
 */
class Application_Backoffice_IosautopublishController extends Backoffice_Controller_Default
{
    /**
     * Check iTunes credentials validity and returns teams on success!
     *
     * - On success also saves cypheredCredentials on database!
     * - On failure doesn't save credentials!
     */
    public function savecredentialsAction ()
    {
        try {
            $request = $this->getRequest();
            $params = Siberian_Json::decode($request->getRawBody());

            if (empty($params)) {
                throw new Siberian_Exception('#325-01: ' . __('Missing parameters.'));
            }

            if (empty($params['app_id'])) {
                throw new Siberian_Exception('#325-02: ' . __('App Id is required!'));
            }

            if (empty($params['login']) || empty($params['password'])) {
                throw new Siberian_Exception('#325-03: ' . __('Please fill iTunes Connect Credentials.'));
            }

            if ($params['password'] === Application_Model_IosAutopublish::$fakePassword) {
                // Forward to refresh teams!
                return $this->forward('refreshteams');
            }

            $payload = (new Application_Model_IosAutopublish)
                ->getTeams($params['login'], $params['password']);

            // Save if success!
            if (array_key_exists('success', $payload) &&
                array_key_exists('cypheredCredentials', $payload)) {
                $appIosAutopublish = (new Application_Model_IosAutopublish())
                    ->find($params['app_id'],'app_id');

                $appIosAutopublish
                    ->setAppId($params['app_id'])
                    ->setCypheredCredentials($payload['cypheredCredentials'])
                    ->setTeams(Siberian_Json::encode([
                        'teams' => $payload['teams'],
                        'itcTeams' => $payload['itcTeams']
                    ]))
                    ->setItunesLogin($params['login'])
                    ->setItunesPassword('') // Clear old "clear" login
                    ->save();

                $payload['message'] = __('Credentials successfully saved!');
                $payload['teams'] = $appIosAutopublish->getTeamsArray();
                $payload['itcProviders'] = $appIosAutopublish->getItcProvidersArray();
                $payload['selected_team'] = $appIosAutopublish->getTeamId();
                $payload['selected_provider'] = $appIosAutopublish->getItcProvider();
                $payload['stats'] = $appIosAutopublish->getStats();
            }

        } catch (Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     * Refresh the Teams list with current credentials!
     */
    public function refreshteamsAction ()
    {
        try {
            $request = $this->getRequest();
            $params = Siberian_Json::decode($request->getRawBody());

            if (empty($params)) {
                throw new Siberian_Exception('#329-01: ' . __('Missing parameters.'));
            }

            if (empty($params['app_id'])) {
                throw new Siberian_Exception('#329-02: ' . __('App Id is required!'));
            }

            $appIosAutopublish = (new Application_Model_IosAutopublish())
                ->find($params['app_id'],'app_id');

            if (!$appIosAutopublish->getId()) {
                throw new Siberian_Exception('#329-03: ' . __('No credentials found!'));
            }

            $cypheredCredentials = $appIosAutopublish->getCypheredCredentials();
            $itcLogin = $appIosAutopublish->getItunesLogin();
            $itcPassword = $appIosAutopublish->getItunesPassword();

            // In case user didn't saved/repurposed his credentials!
            if (!empty($cypheredCredentials)) {
                $payload = (new Application_Model_IosAutopublish)
                    ->getTeams($appIosAutopublish->getCypheredCredentials());
            } else if (!empty($itcLogin) && !empty($itcPassword)) {
                $payload = (new Application_Model_IosAutopublish)
                    ->getTeams($itcLogin, $itcPassword);

                // Save if success!
                if (array_key_exists('success', $payload) &&
                    array_key_exists('cypheredCredentials', $payload)) {

                    $appIosAutopublish
                        ->setCypheredCredentials($payload['cypheredCredentials'])
                        ->setItunesLogin($itcLogin)
                        ->setItunesPassword('')// Clear old "clear" login
                        ->save();
                }
            }


            // Save if success!
            if (array_key_exists('success', $payload) &&
                array_key_exists('cypheredCredentials', $payload)) {
                $appIosAutopublish
                    ->setTeams(Siberian_Json::encode([
                        'teams' => $payload['teams'],
                        'itcTeams' => $payload['itcTeams']
                    ]))
                    ->save();

                $payload['message'] = __('Teams successfully refreshed!');
                $payload['teams'] = $appIosAutopublish->getTeamsArray();
                $payload['itcProviders'] = $appIosAutopublish->getItcProvidersArray();
                $payload['selected_team'] = $appIosAutopublish->getTeamId();
                $payload['selected_provider'] = $appIosAutopublish->getItcProvider();
                $payload['stats'] = $appIosAutopublish->getStats();
            }

        } catch (Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     * saving ios autopublish settings!
     */
    public function saveinfoiosautopublishAction()
    {
        try {
            $request = $this->getRequest();
            $params = Siberian_Json::decode($request->getRawBody());

            if (empty($params)) {
                throw new Siberian_Exception('#330-01: ' . __('Missing parameters.'));
            }

            if (empty($params['app_id'])) {
                throw new Siberian_Exception('#330-02: ' . __('App Id is required!'));
            }

            $appIosAutopublish = (new Application_Model_IosAutopublish())
                ->find($params['app_id'],'app_id');

            if (!$appIosAutopublish->getId()) {
                throw new Siberian_Exception('#330-03: ' . __('No credentials found!'));
            }

            if (empty($params['infos']['languages'])) {
                throw new Siberian_Exception('#330-04: ' . __('Please select at least one language.'));
            }

            $application = (new Application_Model_Application())
                ->find($params['app_id']);

            if (!$application->getId()) {
                throw new Siberian_Exception('#330-05: ' . __('Application not found!'));
            }

            // Find selected team!
            $selectedTeamId = $params['infos']['selected_team'];
            $selectedProviderId = $params['infos']['selected_provider'];

            if (empty($selectedTeamId) || empty($selectedProviderId)) {
                throw new Siberian_Exception('#330-06: ' .
                    __('You must select both a Development Team & an iTunes Connect provider!'));
            }

            $refreshPem = array_key_exists('refresh_pem', $params['infos']);

            $appIosAutopublish
                ->selectTeam($selectedTeamId, $selectedProviderId)
                ->setAppId($params['app_id'])
                ->setWantToAutopublish(1)
                ->setRefreshPem($refreshPem)
                ->setHasAds($params['infos']['has_ads'])
                ->setHasBgLocate($params['infos']['has_bg_locate'])
                ->setHasAudio($params['infos']['has_audio'])
                ->setLanguages(Siberian_Json::encode([
                    $params['infos']["languages"] => true
                ]));

            // Salting token!
            if (!$appIosAutopublish->getToken()) {
                $string = sprintf("%s%s%s%s",
                    $params['app_id'],
                    time(),
                    $appIosAutopublish->getCypheredCredentials(),
                    'mySaltIsTasty!');

                $appIosAutopublish->setToken(sha1($string));
            }

            $appIosAutopublish->save();

            // Build phase!
            $noads = ($appIosAutopublish->getHasAds() == 1) ? '' : 'noads';

            $designCode = $application->getData('design_code');

            $queue = new Application_Model_SourceQueue();

            $queue
                ->setAppId($application->getId())
                ->setName($application->getName())
                ->setType('ios' . $noads)
                ->setDesignCode($designCode)
                ->setIsAutopublish('1')
                ->setIsRefreshPem($refreshPem)
                ->setHost($request->getHttpHost())
                ->setUserId($this->getSession()->getBackofficeUserId())
                ->save();

            $more['zip'] = Application_Model_SourceQueue::getPackages($application->getId());
            $more['queued'] = Application_Model_Queue::getPosition($application->getId());

            # Update status only for builds.
            if (!$refreshPem) {
                $appIosAutopublish->setData('last_build_status', 'pending');
                $appIosAutopublish->save();
            }
#
            $payload = [
                'success' => true,
                'message' => __('Generation successfully queued.'),
                'more' => $more
            ];
        } catch (Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }

    /// To clean after me


    public function updatejobstatusAction() {

        try {
            $request = $this->getRequest();

            $token = $request->getParam('token',null);
            $status = $request->getParam('status',null);
            $error_message = $request->getParam('error_message',null);
            $last_builded_ipa_link = $request->getParam('last_builded_ipa_link',null);


            if (is_null($token) || is_null($status)) {
                throw new Siberian_Exception(__('Missing token and/or status.'));
            }

            $availableStatuses = [
                'pending',
                'queued',
                'building',
                'success',
                'faile',
            ];
            if (!in_array($status, $availableStatuses)) {
                throw new Siberian_Exception(__('Invalid status `%s`.', $status));
            }

            $appIosAutopublish = (new Application_Model_IosAutopublish())
                ->find($token,'token');

            if (!$appIosAutopublish->getId()) {
                throw new Siberian_Exception(__('Unable to find the corresponding build.'));
            }

            switch ($status) {
                case 'success':
                    $appIosAutopublish->setData('last_success', time());
                    $appIosAutopublish->setData('last_finish', time());

                    $application = (new Application_Model_Application())
                        ->find($appIosAutopublish->getId());
                    if (!$application->getId()) {
                        throw new Siberian_Exception(__('Cannot find application from token.'));
                    }
                    //1 is iOS
                    $device = $application->getDevice(1);
                    $device->setData('status_id', 3)->save();
                    break;
                case 'failed':
                    $appIosAutopublish->setData('last_finish', time());
                    break;
            }

            if(!is_null($last_builded_ipa_link)) {
                $appIosAutopublish->setData("last_builded_ipa_link",$last_builded_ipa_link);
            }

            if(!is_null($error_message)) {
                $appIosAutopublish->setData("error_message",base64_decode($error_message));
            }

            $appIosAutopublish->setData("last_build_status",$status);
            $appIosAutopublish->save("last_build_status");

            $data = array(
                "success" => 1,
                "message" => __("OK")
            );

        } catch (Exception $e) {
            // print_r($e->getTrace());
            // die;
            //we limit attack by returning same error in all error case
            $data = array(
                "error" => 1,
                "message" => __($e->getMessage())
            );
        }

        $this->_sendHtml($data);
    }

    public function uploadcertificateAction() {
        $token = $this->getRequest()->getParam("token",null);

        if(is_null($token)) {
            throw new Exception("Wrong params.");
        }

        if (empty($_FILES) || empty($_FILES['file']['name'])) {
            throw new Exception("Wrong params.");
        }

        $appIosAutopublish = new Application_Model_IosAutopublish();
        $appIosAutopublish->find($token,"token");

        $appId = $appIosAutopublish->getData("app_id");

        $application = new Application_Model_Application();
        $application->find($appId);

        if(!$application->getId()) {
            throw new Exception("Wrong params.");
        }

        $base_path = Core_Model_Directory::getBasePathTo("var/apps/iphone/");
        if(!is_dir($base_path)) mkdir($base_path, 0775, true);
        $path = Core_Model_Directory::getPathTo("var/apps/iphone/");
        $adapter = new Zend_File_Transfer_Adapter_Http();
        $adapter->setDestination(Core_Model_Directory::getTmpDirectory(true));

        if ($adapter->receive()) {

            $file = $adapter->getFileInfo();

            $certificat = new Push_Model_Certificate();
            $certificat->find(array('type' => 'ios', 'app_id' => $appId));

            if(!$certificat->getId()) {
                $certificat->setType("ios")
                ->setAppId($appId);
            }

            $new_name = uniqid("cert_").".pem";
            if(!rename($file["file"]["tmp_name"], $base_path.$new_name)) {
                throw new Exception("Wrong params.");
            }

            $certificat->setPath($path.$new_name)->save();

            $data = array(
                "success" => 1,
                "pem_infos" => Push_Model_Certificate::getInfos($appId),
                "message" => __("The file has been successfully uploaded")
            );

        } else {
            throw new Exception("Wrong params.");
        }

        $this->_sendHtml($data);

    }

}
