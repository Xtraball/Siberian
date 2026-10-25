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
     * An action that needs an option value (value_id) but is called without one ends on a PHP Error (null object)
     * or on an exception nobody caught: an ajax request gets a clean JSON error instead of an empty HTTP 500.
     *
     * @param string $action
     */
    public function dispatch($action)
    {
        try {
            parent::dispatch($action);
        } catch (\Throwable $e) {
            $isRouting = $e instanceof Zend_Controller_Action_Exception ||
                $e instanceof Zend_Controller_Dispatcher_Exception;
            if ($isRouting || (!$e instanceof \Error && !$this->getRequest()->isXmlHttpRequest())) {
                throw $e;
            }

            error_log(sprintf('%s: %s in %s:%d', $this->getFullActionName('/'), $e->getMessage(), $e->getFile(), $e->getLine()));
            $message = $e instanceof \Error ?
                __('The request could not be processed.') : $e->getMessage();
            if ($this->_current_option_value === null && $e instanceof \Error) {
                $message = __('This request needs a valid feature (value_id).');
            }

            $this->getResponse()
                ->setHttpResponseCode(400)
                ->setHeader('Content-Type', 'application/json', true)
                ->setBody(Siberian_Json::encode([
                    'error' => true,
                    'message' => $message,
                ], JSON_PRETTY_PRINT));
        }
    }

    /**
     * An ajax action that ends without any response (a save without the data it reads) used to fall through to the
     * "noroute" page, which renders a whole page for nothing: answer an error instead.
     */
    public function postDispatch()
    {
        $request = $this->getRequest();
        if ($request->isXmlHttpRequest() &&
            !$this->getLayout()->isLoaded() &&
            $request->isDispatched() &&
            !$this->getResponse()->isRedirect() &&
            $request->getActionName() !== 'noroute') {
            $this->_sendJson([
                'error' => true,
                'message' => __('The request contains no data, or data this action does not accept.'),
            ]);
        }

        parent::postDispatch();
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
