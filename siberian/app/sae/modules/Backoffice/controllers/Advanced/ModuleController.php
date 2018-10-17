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
                'can_uninstall = ?' => 0,
                'type NOT IN (?)' => ['template']
            ],
            [
                'name ASC'
            ]
        );
        $installed_modules = (new Installer_Model_Installer_Module())->findAll(
            [
                'can_uninstall = ?' => 1,
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
            $data[$type][] = [
                'id' => $installed_module->getId(),
                'name' => __($installed_module->getData('name')),
                'original_name' => $installed_module->getData('name'),
                'version' => $installed_module->getData('version'),
                'actions' => Siberian_Module::getActions($installed_module->getData('name')),
                'created_at' => $installed_module->getFormattedCreatedAt(),
                'updated_at' => $installed_module->getFormattedUpdatedAt(),
            ];
        }

        foreach ($templates as $template) {
            $data['templates'][] = [
                'id' => $template->getId(),
                'name' => __($template->getData('name')),
                'original_name' => $template->getData('name'),
                'version' => $template->getData('version'),
            ];
        }

        foreach ($features as $feature) {
            $data['features'][] = [
                'id' => $feature->getId(),
                'name' => $feature->getName(),
                'code' => $feature->getCode(),
                'description' => $feature->getBackofficeDescription(),
                'is_enabled' => (boolean) $feature->getIsEnabled(),
            ];
        }

        $this->_sendJson($data);

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
