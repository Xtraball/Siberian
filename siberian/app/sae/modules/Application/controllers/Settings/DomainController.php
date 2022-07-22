<?php

use Siberian\Version;
use Siberian\Exception;

/**
 * Class Application_Settings_DomainController
 */
class Application_Settings_DomainController extends Application_Controller_Default
{

    /**
     *
     */
    public function indexAction()
    {
        $this->loadPartials();
    }

    /**
     *
     */
    public function saveAction()
    {
        try {
            $request = $this->getRequest();
            $application = $this->getApplication();
            $params = $request->getPost();
            
            if (!array_key_exists("domain", $params)) {
                throw new Exception(p__("application", "Missing domain name."));
            }

            // Compare with main domain!
            $mainDomain = __get("main_domain");
            $hostname = trim($params["domain"]);

            if (!empty($hostname)) {
                if ($hostname === $mainDomain) {
                    throw new Exception(p__("application", "This domain is reserved."));
                }

                // Ok we can continue
                // Checking CNAME
                // @todo or not.

                // Searching inside apps
                $appUseIt = (new Application_Model_Application())->findAll([
                    "domain = ?" => $hostname,
                    "app_id != ?" => $application->getId()
                ]);
                if ($appUseIt->count() > 0) {
                    throw new Exception(p__("application", "This domain is already used by another application."));
                }

                // Searching inside white labels (if pe)
                if (Version::is("PE")) {
                    $whitelabelUseIt = (new Whitelabel_Model_Editor())->findAll([
                        "host = ?" => $hostname
                    ]);
                    if ($whitelabelUseIt->count() > 0) {
                        throw new Exception(p__("application", "This domain is already used by another application."));
                    }
                }
            }

            $application->setDomain($hostname);

            // @todo implements smtp key validation.
            //$smtpKey = __get(sprintf("smtp.application.key.%s", $application->getId()));
            if (true /**$smtpKey === $request->getParam("smtp_key")*/) {
                $enableSmtp = filter_var($request->getParam("enable_custom_smtp"), FILTER_VALIDATE_BOOLEAN);
                if ($enableSmtp) {
                    $application->setSmtpCredentials(Siberian_Json::encode($request->getParam("smtp_credentials")));
                }

                $application->setEnableCustomSmtp($enableSmtp);
            }

            $application->save();

            // App link callback dynamic
            $appDomain = $application->getDomain();
            $appKey = $application->getKey();
            $currentDomain = parse_url($request->getBaseUrl(), PHP_URL_HOST);
            if (!empty($appDomain)) {
                $appLink = "http://{$appDomain}";
            } else {
                $appLink = "https://{$currentDomain}/{$appKey}";
            }
        
            $payload = [
                "success" => true,
                "href" => $appLink,
                "message" => __("Success"),
            ];
        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage(),
            ];
        }
        
        $this->_sendJson($payload);
    }

    /**public function testEmailAction()
    {
        try {
            $request = $this->getRequest();
            $application = $this->getApplication();

            $smtpKey = __get(sprintf("smtp.application.key.%s", $application->getId()));

            if ($smtpKey === $request->getParam("smtp_key")) {
                $enableSmtp = filter_var($request->getParam("enable_custom_smtp"), FILTER_VALIDATE_BOOLEAN);
                if ($enableSmtp) {
                    $application->setSmtpCredentials(Siberian_Json::encode($request->getParam("smtp_credentials")));
                }
                $application->setEnableCustomSmtp($enableSmtp);
            }


            $payload = [
                "success" => true,
                "message" => __("Success"),
            ];
        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }*/

    /**
     *
     */
    public function checkcnameAction()
    {

        if ($this->getRequest()->isPost()) {

            try {

                $code = 1;
                $application = $this->getApplication();
                if ($application->getDomain() AND Core_Model_Url::checkCname($application->getDomain())) {
                    $code = 0;
                }

            } catch (Exception $e) {
                $code = 1;
            }
            $html = Zend_Json::encode(array('code' => $code));
            $this->getLayout()->setHtml($html);
        }

    }


}
