<?php

/**
 * Class Installer_InstallationController
 */
class Installer_InstallationController extends Installer_Controller_Installation_Default
{
    /**
     *
     */
    public function indexAction()
    {
        $this->loadPartials();
    }

    /**
     * @throws Zend_Controller_Response_Exception
     */
    public function savelicenseAction()
    {
        try {
            if (Installer_Model_Installer::isInstalled() === false) {
                $request = $this->getRequest();

                __set('siberiancms_key', $request->getParam('key'));

                $payload = [
                    'success' => true
                ];
            } else {
                throw new Siberian_Exception("An error occured while saving the license.");
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
     * @throws Zend_Controller_Response_Exception
     */
    public function endAction()
    {
        try {
            if (Installer_Model_Installer::setIsInstalled()) {
                $protocol = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
                Siberian_Autoupdater::configure($protocol . $this->getRequest()->getHttpHost());

                // Save installation date & version!
                __set('installation_date', date("Y-m-d H:i:s"), 'Installation date');
                __set('installation_version', Siberian_Version::VERSION, 'Installation version');

                $payload = [
                    'success' => true
                ];
            } else {
                throw new Siberian_Exception("An error occured while finalizing the installation.");
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