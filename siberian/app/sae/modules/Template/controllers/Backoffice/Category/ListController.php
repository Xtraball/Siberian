<?php

/**
 * Class Template_Backoffice_Category_ListController
 */
class Template_Backoffice_Category_ListController extends Backoffice_Controller_Default
{
    public function loadAction()
    {
        $payload = [
            'title' => sprintf('%s > %s',
                __('Manage'),
                __('Templates')
            ),
            'icon' => 'fa-picture-o',
        ];

        $this->_sendJson($payload);
    }

    public function findallAction()
    {
        $categories = (new Template_Model_Category())
            ->findAll();

        $templates = (new Template_Model_Design())
            ->findAll(
                [
                    'version = ?' => 2,
                ],
                [
                    'position ASC',
                    'name ASC'
                ]);

        $payload = [
            'title' => __('List of your categories'),
            'categories' => [],
            'templates' => [],
        ];

        foreach ($categories as $category) {
            $payload['categories'][] = [
                'category_id' => $category->getId(),
                'original_name' => __($category->getData('original_name')),
                'name' => $category->getName(),
            ];
        }

        $coreTemplates = [
            'blank',
            'bleuc',
            'rouse',
            'colors',
        ];

        $toggleTemplates = [
            'blank',
        ];

        foreach ($templates as $template) {

            // Fetching install path with overview_new
            $overviewNew = $template->getOverviewNew();
            preg_match("#^\/app\/[a-z]+\/modules\/([a-z]+)\/.*#i", $overviewNew, $matches);
            if (isset($matches[1]) &&
                !Installer_Model_Installer_Module::sGetIsEnabled($matches[1])) {
                // Skip disabled templates!
                continue;
            }

            $payload['templates'][] = [
                'template_id' => $template->getId(),
                'name' => $template->getName(),
                'is_active' => (boolean) $template->getIsActive(),
                'is_protected' => in_array($template->getcode(), $coreTemplates),
                'overview' => $template->getOverviewNew(),
                'can_toggle' => !in_array($template->getcode(), $toggleTemplates),
            ];
        }

        $this->_sendJson($payload);
    }

    public function saveAction()
    {
        try {
            if (__getConfig('is_demo')) {
                // Demo version
                throw new \Siberian\Exception(__("This is a demo version, these changes can't be saved."));
            }

            $request = $this->getRequest();
            $data = $request->getBodyParams();

            if (empty($data)) {
                throw new \Siberian\Exception(__('Missing params!'));
            }

            $categories = $data['categories'];
            foreach ($categories as $categoryData) {
                $category = (new Template_Model_Category())
                    ->find($categoryData['category_id']);
                $category
                    ->setName($categoryData['name'])
                    ->save();
            }
            
            $payload = [
                'success' => true,
                'message' => __('Success'),
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }
        
        $this->_sendJson($payload);
    }

}
