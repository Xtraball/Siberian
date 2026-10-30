<?php

/**
 * Class Backoffice_Advanced_ModuleController
 */
class Backoffice_Advanced_ModuleController extends Backoffice_Controller_Default
{

    public function loadAction()
    {
        $payload = [
            'title' => sprintf('%s > %s > %s',
                __('Settings'),
                __('Advanced'),
                __('Modules')),
            'icon' => 'fa-sliders',
        ];

        $this->_sendJson($payload);
    }

    public function findallAction()
    {

        $core_modules = (new Installer_Model_Installer_Module())->findAll(
            [
                'name IN (?)' => \Siberian_Module::$coreModules,
                'type NOT IN (?)' => ['template']
            ],
            [
                'name ASC'
            ]
        );
        $installed_modules = (new Installer_Model_Installer_Module())->findAll(
            [
                'name NOT IN (?)' => \Siberian_Module::$coreModules,
                'type NOT IN (?)' => ['template']
            ],
            [
                'name ASC'
            ]
        );

        $templates = (new Installer_Model_Installer_Module())->findAll(
            [
                'type IN (?)' => ['template']
            ],
            [
                'name ASC'
            ]
        );

        $features = (new Application_Model_Option())->findAll(
            [],
            [
                'name ASC'
            ]
        );

        $data = [
            'core_modules' => [],
            'modules' => [],
            'layouts' => [],
            'templates' => [],
            'features' => [],
            'icons' => [],
        ];

        foreach ($core_modules as $core_module) {
            $data['core_modules'][] = [
                'id' => $core_module->getId(),
                'name' => __($core_module->getData('name')),
                'original_name' => $core_module->getData('name'),
                'version' => $core_module->getData('version'),
                'actions' => Siberian_Module::getActions($core_module->getData('name')),
                'created_at' => $core_module->getFormattedCreatedAt(),
                'updated_at' => $core_module->getFormattedUpdatedAt(),
            ];
        }

        foreach ($installed_modules as $installed_module) {
            switch ($installed_module->getData('type')) {
                case 'layout':
                    $type = 'layouts';
                    break;
                case 'icons':
                    $type = 'icons';
                    break;
                default:
                case 'module':
                    $type = 'modules';
                    break;

            }
            $name = $installed_module->getData('name');
            $code = $installed_module->getCode();

            $useLicense = (bool) $installed_module->getUseLicense();
            $licenseKey = p__('backoffice', 'n.a.');
            if ($useLicense) {
                $licenseKey = substr(__get($code . '_key'), 0, 8) . '**********';
            }

            $data[$type][] = [
                'id' => $installed_module->getId(),
                'name' => __($name),
                'original_name' => $name,
                'version' => $installed_module->getData('version'),
                'actions' => Siberian_Module::getActions($name),
                'created_at' => $installed_module->getFormattedCreatedAt(),
                'updated_at' => $installed_module->getFormattedUpdatedAt(),
                'use_license' => $useLicense,
                'license_key' => $licenseKey,
                'is_enabled' => Installer_Model_Installer_Module::sGetIsEnabled($name),
                'quarantine' => \Siberian\Module\Quarantine::get($name),
            ];
        }

        foreach ($templates as $template) {
            $data['templates'][] = [
                'id' => $template->getId(),
                'name' => __($template->getData('name')),
                'original_name' => $template->getData('name'),
                'version' => $template->getData('version'),
                'is_enabled' => Installer_Model_Installer_Module::sGetIsEnabled($template->getData('name')),
            ];
        }

        foreach ($features as $feature) {
            $data['features'][] = [
                'id' => $feature->getId(),
                'name' => $feature->getName(),
                'code' => $feature->getCode(),
                'description' => $feature->getBackofficeDescription(),
                'is_enabled' => (bool) $feature->getIsEnabled(),
            ];
        }

        $this->_sendJson($data);

    }

    /**
     * The modules in quarantine, for the dashboard.
     */
    public function quarantineAction()
    {
        $this->_sendJson(\Siberian\Module\Quarantine::summary());
    }

    /**
     * Checks a quarantined module again (or all of them), in a separate process, and releases it when it loads.
     */
    public function quarantinerecheckAction()
    {
        try {
            $params = $this->_quarantineParams();
            $results = [];
            foreach ($this->_quarantineModules($params) as $module) {
                [$status, $message] = \Siberian\Module\Quarantine::recheck($module);
                $results[$module] = ['status' => $status, 'message' => $message];
            }
            $this->_quarantineClearCache();

            $released = count(array_filter($results, function ($result) {
                return $result['status'] === 'released';
            }));
            $payload = [
                'success' => true,
                'message' => p__('backoffice', 'Recheck done, %s released.', $released),
                'results' => $results,
                'summary' => \Siberian\Module\Quarantine::summary(),
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     * Enables a quarantined module again, it goes back to quarantine if it breaks the application again.
     */
    public function quarantinereleaseAction()
    {
        try {
            $params = $this->_quarantineParams();
            foreach ($this->_quarantineModules($params) as $module) {
                \Siberian\Module\Quarantine::release($module);
            }
            $this->_quarantineClearCache();

            $payload = [
                'success' => true,
                'message' => p__('backoffice', 'The module is enabled again.'),
                'summary' => \Siberian\Module\Quarantine::summary(),
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     * Starts a compatibility scan of all the modules of app/local/modules in the background.
     */
    public function quarantinescanAction()
    {
        $started = \Siberian\Module\Quarantine::startScan();
        $payload = [
            ($started ? 'success' : 'error') => true,
            'message' => $started ?
                p__('backoffice', 'The scan started, it can take a few minutes, reload this page to see the result.') :
                p__('backoffice', 'The scan could not be started, run "php cli module:quarantine scan" instead.'),
            'summary' => \Siberian\Module\Quarantine::summary(),
        ];

        $this->_sendJson($payload);
    }

    /**
     * @return array
     */
    private function _quarantineParams(): array
    {
        $raw = trim((string) $this->getRequest()->getRawBody());

        return $raw === '' ? [] : (array) Siberian_Json::decode($raw);
    }

    /**
     * The module of the request, or all the quarantined ones when there is none.
     *
     * @param array $params
     * @return array
     * @throws Siberian_Exception
     */
    private function _quarantineModules(array $params): array
    {
        $module = $params['module'] ?? null;
        if (empty($module)) {
            return array_keys(\Siberian\Module\Quarantine::all());
        }

        if (!in_array($module, \Siberian\Module\Quarantine::localModules(), true)) {
            throw new Siberian_Exception(__("The module you are trying to edit doesn't exists!"));
        }

        return [$module];
    }

    private function _quarantineClearCache()
    {
        Siberian_Cache::__clearCache();
        Siberian_Cache_Design::clearCache();
    }

    public function togglefeatureAction()
    {
        try {
            $params = Siberian_Json::decode($this->getRequest()->getRawBody());
            $featureId = $params['featureId'];
            $isEnabled = filter_var($params['isEnabled'], FILTER_VALIDATE_BOOLEAN);

            if (!$featureId) {
                throw new Siberian_Exception(__('Missing parameters!'));
            }

            $feature = (new Application_Model_Option())
                ->find($featureId);

            if (!$feature->getId()) {
                throw new Siberian_Exception(__("The feature you are trying to edit doesn't exists!"));
            }

            $feature
                ->setIsEnabled($isEnabled)
                ->save();

            $payload = [
                'success' => true,
                'message' => __('Feature is now %s', ($isEnabled) ? __('enabled') : __('disabled'))
            ];
        } catch (Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }

    public function togglemoduleAction()
    {
        try {
            $params = Siberian_Json::decode($this->getRequest()->getRawBody());
            $moduleId = $params['moduleId'];
            $isEnabled = filter_var($params['isEnabled'], FILTER_VALIDATE_BOOLEAN);

            if (!$moduleId) {
                throw new Siberian_Exception(__('Missing parameters!'));
            }

            /**
             * @var $module Installer_Model_Installer_Module
             */
            $module = (new Installer_Model_Installer_Module())
                ->find($moduleId);

            if (!$module->getId()) {
                throw new Siberian_Exception(__("The module you are trying to edit doesn't exists!"));
            }

            $module->toggleIsEnabled($isEnabled);

            // Ensure cache is cleared
            Siberian_Cache::__clearCache();
            Siberian_Cache_Design::clearCache();

            $payload = [
                'success' => true,
                'message' => __('%s is now %s', __(ucfirst($module->getType())), ($isEnabled) ? __('enabled') : __('disabled'))
            ];
        } catch (Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     * Execute a related module action
     */
    public function executeAction()
    {

        $params = Siberian_Json::decode($this->getRequest()->getRawBody());
        $module = $params["module"];
        $action = $params["action"];

        try {

            if ($actions = Siberian_Module::getActions($module)) {
                if (isset($actions[$action])) {
                    $module_action = $actions[$action];

                    if (strpos($module_action["action"], "::") !== false) {
                        $parts = explode("::", $module_action["action"]);
                        $class = $parts[0];
                        $method = $parts[1];
                        if (class_exists($class) && method_exists($class, $method) && call_user_func($parts)) {
                            $data = [
                                "success" => 1,
                                "message" => __("Action '{$action}' executed for module '{$module}'."),
                            ];
                        } else {
                            throw new Exception(__("Unknown action for this module."));
                        }
                    } else {
                        throw new Exception(__("Unknown action for this module."));
                    }
                } else {
                    throw new Exception(__("Unknown action for this module."));
                }
            } else {
                throw new Exception(__("Unknown action for this module."));
            }

        } catch (Exception $e) {
            $data = [
                "error" => 1,
                "message" => $e->getMessage(),
            ];
        }

        $this->_sendHtml($data);
    }

}
