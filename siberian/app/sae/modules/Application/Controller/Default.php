<?php

class Application_Controller_Default extends Admin_Controller_Default {

    protected $_current_option_value;

    public function init() {

        parent::init();

        $request = $this->getRequest();
        if ($request->getControllerName() == 'privacypolicy') {
            return $this;
        }

        // Options ACL
        $application_acl_option = new Application_Model_Acl_Option();
        $denied_options = $application_acl_option->findAllByAppAndAdminId($this->getApplication()->getId(),$this->getAdmin()->getId());
        $this->_getAcl()->denyResources($denied_options, true);

        $excluded = array(
            'admin_application_list',
            'admin_application_new',
            'admin_application_set',
            'admin_application_createpost',
            'front_index_noroute',
            'front_index_error',
        );

        // Test si un id de value est passé en paramètre
        if($id = $this->getRequest()->getParam('option_value_id') OR
            $id = $this->getRequest()->getParam('value_id')) {
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

    /**
     * Generic edit Action, loading form for features
     */
    public function editAction() {
        try {
            if ($this->getCurrentOptionValue()) {
                $this->loadPartials(null, false);
                $layout = $this->getLayout();

                // Vars assigned to the view automatically!
                $contentPartial = $layout->getPartial('content');
                $this->assignVars($contentPartial);

                if ($layout->getPartial('content_editor')) {
                    // Vars assigned to the view automatically!
                    $contentEditorPartial = $layout->getPartial('content_editor');
                    $this->assignVars($contentEditorPartial);
                }

                $htmlRender = mb_convert_encoding($this->getLayout()->render(), 'UTF-8', 'UTF-8');

                if (empty($htmlRender)) {
                    // Try to rebuild cache, files may have changed upon update!
                    Siberian_Cache_Design::__clearCache();
                    Siberian_Cache_Design::init();

                    $htmlRender = mb_convert_encoding($this->getLayout()->render(), 'UTF-8', 'UTF-8');
                }

                $overviewPath = $this->getCurrentOptionValue()->getPath(null, array(), 'mobile');

                $payload = [
                    'html' => $htmlRender,
                    'path' => $overviewPath ? $overviewPath : ''
                ];
            } else {
                $payload = [
                    'error' => true,
                    'message' => __('The feature doesn\'t esixts')
                ];
            }
        } catch(Exception $e) {
            $payload = [
                'error' => true,
                'message' => __('An unknown error occurred, please try again later.')
            ];
        }

        $this->_sendJson($payload);
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
     * @duplicate with sae/mae
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
