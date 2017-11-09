<?php

class Application_Mobile_TemplateController extends Application_Controller_Mobile_Default {

    public function findallAction() {

        $pages = $this->getApplication()->getOptions();

        $path = $this->getApplication()->getPath()."/";
        $partials = array();
        $paths = array();

        $option_layouts = array();
        $option_layout = new Application_Model_Option_Layout();
        $layouts = $option_layout->findAll();

        foreach($layouts as $layout) {
            $option_layouts[] = $layout->getOptionId();
        }

        foreach($pages as $page) {
            if(!$page->isActive()) continue;
            if(!$page->getIsAjax() AND $page->getObject()->getLink()) continue;

            $module_name = current(explode("_", $page->getModel()));
            if(!empty($module_name)) {
                $module_name = strtolower($module_name);
                Core_Model_Translator::addModule($module_name);
            }

            $suffix = "_l{$page->getLayoutId()}";
            $paths = array_merge($paths, $page->getObject()->getTemplatePaths($page, $option_layouts, $suffix, $path));
        }

        if($this->getApplication()->usesUserAccount()) {
            Core_Model_Translator::addModule("customer");
            $account_partials = array("customer/mobile_account_login/template", "customer/mobile_account_register/template",
                "customer/mobile_account_edit/template", "customer/mobile_account_forgottenpassword/template");

            foreach($account_partials as $account_partial) {
                $layout = str_replace("/", "_", $account_partial."_l1");
                $layout_id = $path.$account_partial;
                $paths[] = array("layout" => $layout, "layout_id" => $layout_id);
            }
        }

        foreach($paths as $path) {
            $this->loadPartials($path["layout"], false);
            $partials[$path["layout_id"]] = $this->getLayout()->render();
            $this->getLayout()->unload();
        }

        $this->_sendHtml($partials);
    }

}