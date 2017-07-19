<?php

class IndexController extends Core_Controller_Default {

    public function indexAction() {
        $layout_id = null;

        $request = $this->getRequest();

        if(!$request->isApplication()) {
            if ($request->isInstalling()) {
                $layout_id = 'installer_installation_index';
                // Do not redirect here
            }
            else if (!$this->getSession()->isLoggedIn()) {
                $layout_id = 'admin_account_login';
                $this->_redirect('admin/account/login');
            }
            else {
                // redirect to get to the correct page with ACL
                $this->_redirect("application/customization/index");
            }
            $module = substr($layout_id, 0, stripos($layout_id, '_'));
            Core_Model_Translator::addModule($module);
        } else if($this->getApplication()->useIonicDesign()) {

            $ionic_url = $this->getApplication()
                ->getIonicUrl(null, array(), Core_Model_Language::getCurrentLanguage());
            $path_info = $request->getPathInfo();

            if($request->getParam('login_fb', false) !== false) {
                $url = sprintf("%s?__tokenfb__=", $ionic_url);
                echo "<script type='text/javascript'>
    var token = window.location.hash.match(/\#access_token=(.*)&expires_in/);
    token = token[1];
    window.location = '" . $url . "' + token;
</script>";
                die;
            }

            if(!empty($path_info) and $path_info != "/") {
                $ionic_url = $ionic_url . "?__goto__=" . $path_info;
            }

            if($request->getParam('overview', false) !== false) {
                $ionic_url = str_replace('/browser/', '/overview/', $ionic_url);
            }

            $this->getResponse()
                ->setRedirect($ionic_url)
                ->sendResponse()
            ;
            die;
        } else {

            if($this->getApplication()->getLayoutVisibility() != Application_Model_Layout_Homepage::VISIBILITY_HOMEPAGE AND !$this->hasParam("value_id")) {

                $page = $this->getApplication()->getFirstActivePage();
                if(!$page->getId()) {
                    $page = Application_Model_Option_Value::getDummy();
                }

                $this->_redirect($page->getMobileUri(), array('value_id' => $page->getId()));
                return $this;
            } else {
                Core_Model_Translator::addModule("padlock");
            }
        }

        $this->loadPartials($layout_id);
    }

    private function _loadAllPartials() {

        $pages = $this->getApplication()->getOptions();
        $baseUrl = $this->getApplication()->getUrl()."/";
        $partials = array();
        $origLayout = clone $this->getLayout();
        $this->_layout = clone $this->getLayout();
        $this->_layout->unload();

        foreach($pages as $page) {
            if(!$page->isActive()) continue;
            if(!$page->getIsAjax() AND $page->getObject()->getLink()) continue;

            $suffix = "_l{$page->getLayoutId()}";

            $layout = str_replace(array($baseUrl, "/"), array("", "_"), $page->getUrl("template").$suffix);
            $layout_id = str_replace($baseUrl, "", $this->getApplication()->getPath()."/".$page->getUrl("template"));
            $this->loadPartials($layout, false);
            $partials[$layout_id] = $this->getLayout()->render();
        }

        $this->_layout = $origLayout;

        return $partials;
    }

}