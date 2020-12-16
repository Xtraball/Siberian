<?php

use Siberian\Security;

/**
 * Class Application_Controller_Default
 */
class Application_Controller_Default extends Admin_Controller_Default
{

    /**
     * @var
     */
    protected $_current_option_value;

    /**
     * @var array
     */
    public $openActions = [];

    /**
     * @return $this|Admin_Controller_Default|void
     * @throws Zend_Exception
     * @throws Zend_Session_Exception
     * @throws \Siberian\Exception
     */
    public function init()
    {
        parent::init();

        $request = $this->getRequest();
        $session = $this->getSession();
        $application = $this->getApplication();

        // Guest routes (doesn't require active auth)
        $allowed = Security::$routesGuest;
        if ($request->getControllerName() === 'privacypolicy' ||
            in_array($this->getFullActionName('_'), $allowed, false)) {
            return $this;
        }

        foreach ($this->openActions as $openAction) {
            if ($request->getModuleName() === $openAction['module'] &&
                $request->getControllerName() === $openAction['controller'] &&
                $request->getActionName() === $openAction['action']) {
                return $this;
            }
        }

        // Options ACL
        $deniedOptions = (new Application_Model_Acl_Option())
            ->findAllByAppAndAdminId($application->getId(), $this->getAdmin()->getId());

        $this->_getAcl()->denyResources($deniedOptions, true);

        // Retry after application/admin acl
        if ($this->_canAccessCurrentPage() === false) {
            $this->_forward("forbidden");
            return;
        }

        // Test si un id de value est passé en paramètre
        $id = $request->getParam('option_value_id', $request->getParam('value_id', false));
        if ($id !== false) {
            // Créé et charge l'objet
            $this->_current_option_value = new Application_Model_Option_Value();
            $this->_current_option_value->find($id);
        }

        $session->editing_app_id = $application->getId();

        $admin_id = null;
        if ($session->getAdmin() && $session->getAdmin()->getId()) {
            $admin_id = $session->getAdmin()->getId();
        }

        if ($application->isSomeoneElseEditingIt($admin_id)) {
            $session->addWarning(
                __("Careful, someone else is working on this application."),
                "two_editing_the_same_app");
        }
    }

    /**
     * Generic edit Action, loading form for features
     */
    public function editAction()
    {
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
        } catch (Exception $e) {
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
    public function getCurrentOptionValue()
    {
        return $this->_current_option_value;
    }

    /**
     * Assign view public vars to template
     *
     * @duplicate with sae/mae
     *
     * @param $partial
     */
    public function assignVars($partial)
    {
        if (!isset($partial)) {
            return;
        }

        if ($this->getCurrentOptionValue()) {
            $partial->setOptionValue($this->getCurrentOptionValue());
        }

        foreach (get_object_vars($this->view) as $name => $value) {
            if (substr($name, 0, 1) == '_') {
                continue;
            }
            $partial->setData($name, $value);
        }
    }

}
