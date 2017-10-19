<?php

class Application_Controller_Default extends Admin_Controller_Default {

    protected $_current_option_value;

    public function init() {

        parent::init();

        // Options ACL
        $application_acl_option = new Application_Model_Acl_Option();
        $denied_options = $application_acl_option->findAllByAppAndAdminId($this->getApplication()->getId(),$this->getAdmin()->getId());
        $this->_getAcl()->denyResources($denied_options, true);

        // Subscription ACL
        if($this->isPe()) {
            if ($subscription_id = $this->getApplication()->getSubscription()->getSubscriptionId()) {
                $subscription_acl = new Subscription_Model_Acl_Resource();
                $resources = $subscription_acl->findBySubscriptionId($subscription_id);

                $codes = array();
                foreach ($resources as $resource) {
                    $codes[] = $resource->getCode();
                }

                if ($codes) {
                    $this->_getAcl()->denyResources($codes);
                }
            }
        }

        $excluded = array(
            'admin_application_list',
            'admin_application_new',
            'admin_application_set',
            'admin_application_createpost',
            'front_index_noroute',
            'front_index_error',
        );

        // Test si un id de value est passé en paramètre
        if($id = $this->getRequest()->getParam('option_value_id') OR $id = $this->getRequest()->getParam('value_id')) {
            // Créé et charge l'objet
            $this->_current_option_value = new Application_Model_Option_Value();
            $this->_current_option_value->find($id);
        }

        $this->getSession()->editing_app_id = $this->getApplication()->getId();

        $admin_id = null;
        if($this->getSession()->getAdmin() && $this->getSession()->getAdmin()->getId()) {
            $admin_id = $this->getSession()->getAdmin()->getId();
        }

        if($this->getApplication()->isSomeoneElseEditingIt($admin_id)) {
            $this->getSession()->addWarning(__("Careful, someone else is working on this application."), "two_editing_the_same_app");
        }
    }

    public function editAction() {

        if($this->getCurrentOptionValue()) {
            $this->loadPartials(null, false);

            $layout = $this->getLayout();
            $content_partial = $layout->getPartial('content');
            $this->assignVars($content_partial);

            if($layout->getPartial('content_editor')) {
                $content_editor_partial = $layout->getPartial('content_editor');
                $this->assignVars($content_editor_partial);
            }
            $html = array('html' => mb_convert_encoding($this->getLayout()->render(), 'UTF-8', 'UTF-8'));
            $path =  $this->getCurrentOptionValue()->getPath(null, array(), "mobile");
            $html["path"] = $path ? $path : "";
            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    /**
     * @return Application_Model_Option_Value
     */
    public function getCurrentOptionValue() {
        return $this->_current_option_value;
    }

    /**
     * Assign view public vars to template
     *
     * @param $partial
     */
    public function assignVars($partial) {
        if(!isset($partial)) {
            return;
        }

        if($this->getCurrentOptionValue()) {
            $partial->setOptionValue($this->getCurrentOptionValue());
        }

        foreach(get_object_vars($this->view) as $name => $value) {
            if (substr($name, 0, 1) == '_') {
                continue;
            }
            $partial->setData($name, $value);
        }
    }

}
