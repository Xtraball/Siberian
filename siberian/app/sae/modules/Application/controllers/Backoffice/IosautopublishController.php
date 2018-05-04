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
                // Abort!
                throw new Siberian_Exception('#325-04: ' . __('Password not changed!'));
            }

            $payload = (new Application_Model_IosAutopublish)
                ->getTeams($params['login'], $params['password']);

            // Save if success!
            if (array_key_exists('success', $payload) && array_key_exists('cypheredCredentials', $payload)) {
                $appIosAutopublish = (new Application_Model_IosAutopublish())
                    ->find($params['app_id'],'app_id');

                $selectedTeamId = '';
                $selectedTeamName = '';
                if (sizeof($payload['teams']) === 1) {
                    $selectedTeamId = $payload['teams'][0]['teamId'];
                    $selectedTeamName = $payload['teams'][0]['name'];
                }

                $appIosAutopublish
                    ->setAppId($params['app_id'])
                    ->setCypheredCredentials($payload['cypheredCredentials'])
                    ->setTeams(Siberian_Json::encode($payload['teams']))
                    ->setTeamId($selectedTeamId)
                    ->setTeamName($selectedTeamName)
                    ->setItunesLogin($params['login'])
                    ->setItunesPassword('') // Clear old "clear" login
                    ->save();

                $payload['message'] = __('Credentials successfully saved!');
                $payload['teams'] = $appIosAutopublish->getTeamsArray();
                $payload['selected_team'] = $appIosAutopublish->getSelectedTeam();
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

            $payload = (new Application_Model_IosAutopublish)
                ->getTeams($appIosAutopublish->getCypheredCredentials());

            // Save if success!
            if (array_key_exists('success', $payload) && array_key_exists('cypheredCredentials', $payload)) {
                $selectedTeamId = '';
                $selectedTeamName = '';
                if (sizeof($payload['teams']) === 1) {
                    $selectedTeamId = $payload['teams'][0]['teamId'];
                    $selectedTeamName = $payload['teams'][0]['name'];
                }

                $appIosAutopublish
                    ->setTeams(Siberian_Json::encode($payload['teams']))
                    ->setTeamId($selectedTeamId)
                    ->setTeamName($selectedTeamName)
                    ->save();

                $payload['message'] = __('Teams successfully refreshed!');
                $payload['teams'] = $appIosAutopublish->getTeamsArray();
                $payload['selected_team'] = $appIosAutopublish->getSelectedTeam();
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
            $selectedTeamName = '';
            $teams = $appIosAutopublish->getTeamsArray();
            foreach ($teams as $team) {
                if ($team['teamId'] === $selectedTeamId) {
                    $selectedTeamName = $team['name'];
                    break;
                }
            }

            $appIosAutopublish
                ->setAppId($params['app_id'])
                ->setWantToAutopublish(1)
                ->setHasAds($params['infos']['has_ads'])
                ->setHasBgLocate($params['infos']['has_bg_locate'])
                ->setHasAudio($params['infos']['has_audio'])
                ->setTeamId($selectedTeamId)
                ->setTeamName($selectedTeamName)
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
                ->setHost($request->getHttpHost())
                ->setUserId($this->getSession()->getBackofficeUserId())
                ->save();

            $more['zip'] = Application_Model_SourceQueue::getPackages($application->getId());
            $more['queued'] = Application_Model_Queue::getPosition($application->getId());

            $appIosAutopublish->setData('last_build_status', 'pending');
            $appIosAutopublish->save();

            $payload = [
                'success' => true,
                'message' => 'Generation successfully queued.',
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

            $token = $this->getRequest()->getParam("token",null);
            $status = $this->getRequest()->getParam("status",null);
            $error_message = $this->getRequest()->getParam("error_message",null);
            $last_builded_ipa_link = $this->getRequest()->getParam("last_builded_ipa_link",null);


            if(is_null($token) || is_null($status)) {
                throw new Exception("Wrong params.");
            }

            if(!in_array($status, array("pending","queued","building","success","failed"))) {
                throw new Exception("Wrong params.");
            }

            $appIosAutopublish = new Application_Model_IosAutopublish();
            $appIosAutopublish->find($token,"token");

            if(!is_numeric($appIosAutopublish->getId())) {
                throw new Exception("Wrong params.");
            }

            switch ($status) {
                case 'success':
                    $appIosAutopublish->setData("last_success",time());
                    $appIosAutopublish->setData("last_finish",time());

                    $application = new Application_Model_Application();
                    $application->find($appIosAutopublish->getId());
                    if(!$application->getId()) {
                        throw new Exception("Cannot get application from token.");
                    }
                    //1 is iOS
                    $device = $application->getDevice(1);
                    $device->setData("status_id",3)->save();
                    break;
                case 'failed':
                    $appIosAutopublish->setData("last_finish",time());
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
