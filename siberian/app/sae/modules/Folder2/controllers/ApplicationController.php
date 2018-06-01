<?php

/**
 * Class Folder2_ApplicationController
 */
class Folder2_ApplicationController extends Application_Controller_Default
{

    /**
     * @var array
     */
    public $cache_triggers = [
        'editpost' => [
            'tags' => [
                'feature_paths_valueid_#VALUE_ID#',
                'assets_paths_valueid_#VALUE_ID#',
                'homepage_app_#APP_ID#',
            ],
        ],
        'editsettings' => [
            'tags' => [
                'feature_paths_valueid_#VALUE_ID#',
                'assets_paths_valueid_#VALUE_ID#',
                'homepage_app_#APP_ID#',
            ],
        ],
        'addfeature' => [
            'tags' => [
                'feature_paths_valueid_#VALUE_ID#',
                'assets_paths_valueid_#VALUE_ID#',
                'homepage_app_#APP_ID#',
            ],
        ],
        'delete' => [
            'tags' => [
                'feature_paths_valueid_#VALUE_ID#',
                'assets_paths_valueid_#VALUE_ID#',
                'homepage_app_#APP_ID#',
            ],
        ],
        'updatepositions' => [
            'tags' => [
                'feature_paths_valueid_#VALUE_ID#',
                'assets_paths_valueid_#VALUE_ID#',
                'homepage_app_#APP_ID#',
            ],
        ],
    ];

    /**
     * Load folder form
     */
    public function loadformAction()
    {
        $request = $this->getRequest();
        $actionName = $request->getParam('actionName');
        $categoryId = $request->getParam('categoryId');
        $parentId = $request->getParam('parentId');
        $valueId = $request->getParam('valueId');

        switch ($actionName) {
            case 'edit':
                $folder = (new Folder2_Model_Category())
                    ->find($categoryId);
                if ($folder->getId()) {
                    $form = new Folder2_Form_Category();
                    $form->getElement('form_header')
                        ->setValue('<h4 class="folder-form-title">' . __('Edit folder') . '</h4>');
                    $form->getElement('title')->setAttrib('rel', $categoryId);
                    $form->populate($folder->getData());

                    $payload = [
                        'success' => true,
                        'form' => $form->render(),
                        'categoryId' => $categoryId,
                        'message' => __('Success.'),
                    ];
                } else {
                    $payload = [
                        'error' => true,
                        'message' => __('The folder/category you are trying to edit doesn\'t exists..'),
                    ];
                }
                break;
            default:
            case 'create':
                $position = (new Folder2_Model_Category())
                    ->getNextCategoryPosition($parentId);

                $folder = new Folder2_Model_Category();
                $folder
                    ->setTitle(__('New subfolder'))
                    ->setTypeId('folder')
                    ->setValueId($valueId)
                    ->setPos($position)
                    ->setParentId($parentId)
                    ->save();

                $folder->setDefaultImages($this->getCurrentOptionValue());

                $form = new Folder2_Form_Category();
                $form->getElement('form_header')
                    ->setValue('<h4 class="folder-form-title">' . __('Edit folder') . '</h4>');
                $form->getElement('title')->setAttrib('rel', $folder->getId());
                $form->populate($folder->getData());

                $categoryId = $folder->getId();
                $placeholder = '
                    <li class="folder-sortable"
                        parentId="' . $parentId . '"
                        rel="' . $categoryId . '">
                        <span class="folder-hover">
                            <i class="folder-sortable-handle fa fa-arrows"></i>
                            <span class="folder-title">' . __('New folder') . '</span>
                            <span class="folder-feature-count"></span>
                            <i class="folder-edit fa fa-pencil pull-right"
                               parentId="' . $parentId . '"
                               rel="' . $categoryId . '"></i>
                            <i class="folder-delete fa fa-remove pull-right"
                               parentId="' . $parentId . '"
                               rel="' . $categoryId . '"></i>
                        <span>
                    </li>';

                $payload = [
                    'success' => true,
                    'form' => $form->render(),
                    'placeholder' => $placeholder,
                    'categoryId' => $categoryId,
                    'message' => __('Success.'),
                ];
                break;
        }

        $this->_sendJson($payload);
    }

    public function updatepositionsAction()
    {
        $request = $this->getRequest();
        $positions = $request->getParam('positions');

        $currentCategory = $positions['category'];
        $currentPositions = $positions['positions'];

        try {
            $category = (new Folder2_Model_Category())
                ->find($currentCategory['categoryId']);

            if ($category->getId()) {
                $category
                    ->setParentId($currentCategory['parentId'])
                    ->save();

                // Update all siblings positions!
                foreach ($currentPositions as $position => $categoryId) {
                    (new Folder2_Model_Category())
                        ->find($categoryId)
                        ->setPos($position)
                        ->save();
                }

                $this->getCurrentOptionValue()
                    ->touch()
                    ->expires(-1);

                $payload = [
                    'success' => true,
                    'message' => __('Folder is up-to-date.')
                ];
            } else {
                $payload = [
                    'error' => true,
                    'message' => __('Folder not found!')
                ];
            }
        } catch (Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }


    /**
     * Simple edit post, validator
     */
    public function editpostAction()
    {
        $values = $this->getRequest()->getPost();

        try {
            $form = new Folder2_Form_Category();
            if ($form->isValid($values)) {
                $optionValue = $this->getCurrentOptionValue();

                $category = (new Folder2_Model_Category())
                    ->find($values['category_id']);

                $category->setData($values);

                if ($values['picture'] === '_delete_') {
                    $category->setData('picture', '');
                } else if (file_exists(Core_Model_Directory::getBasePathTo('images/application' . $values['picture']))) {
                    // Nothing changed, skip!
                } else {
                    $background = Siberian_Feature::moveUploadedFile(
                        $this->getCurrentOptionValue(),
                        Core_Model_Directory::getTmpDirectory() . '/' . $values['picture']);
                    $category->setData('picture', $background);
                }

                if ($values['thumbnail'] === '_delete_') {
                    $category->setData('thumbnail', '');
                } else if (file_exists(Core_Model_Directory::getBasePathTo('images/application' . $values['thumbnail']))) {
                    // Nothing changed, skip!
                } else {
                    $background = Siberian_Feature::moveUploadedFile(
                        $this->getCurrentOptionValue(),
                        Core_Model_Directory::getTmpDirectory() . '/' . $values['thumbnail']);
                    $category->setData('thumbnail', $background);
                }

                // Clear layout_id!
                if ($values['layout_id'] === '-1') {
                    $category->setLayoutId(null);
                }

                $category->save();

                // Update touch date, then never expires (until next touch)!
                $optionValue
                    ->touch()
                    ->expires(-1);

                $payload = [
                    'success' => true,
                    'message' => __('Success.'),
                ];
            } else {
                $payload = [
                    'error' => true,
                    'message' => $form->getTextErrors(),
                    'errors' => $form->getTextErrors(true)
                ];
            }
        } catch (Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     * Simple edit settings, validator
     */
    public function editsettingsAction()
    {
        $values = $this->getRequest()->getPost();

        try {
            $form = new Folder2_Form_Settings();
            if ($form->isValid($values)) {
                $optionValue = $this->getCurrentOptionValue();

                $folder = (new Folder2_Model_Folder())
                    ->find($values['value_id'], 'value_id');

                $folder->setData($values);
                $folder->save();

                // Update touch date, then never expires (until next touch)!
                $optionValue
                    ->touch()
                    ->expires(-1);

                $payload = [
                    'success' => true,
                    'message' => __('Success.'),
                ];
            } else {
                $payload = [
                    'error' => true,
                    'message' => $form->getTextErrors(),
                    'errors' => $form->getTextErrors(true)
                ];
            }
        } catch (Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     * Attach a feature to the given subfolder (category)
     */
    public function addfeatureAction()
    {
        $request = $this->getRequest();

        try {
            $parentId = $request->getParam('parentId');
            $featureId = $request->getParam('featureId');
            $optionValue = $this->getCurrentOptionValue();

            $category = (new Folder2_Model_Category())
                ->find($parentId);

            $feature = (new Application_Model_Option_Value())
                ->find($featureId);

            if (!$category->getId() || !$feature->getId()) {
                throw new Siberian_Exception(__('Unable to find either folder or feature!'));
            }

            $optionFolder = (new Application_Model_Option())
                ->find([
                    'code' => 'folder'
                ]);
            $optionFolder2 = (new Application_Model_Option())
                ->find([
                    'code' => 'folder_v2'
                ]);

            if ($feature->getFolderCategoryId() == $parentId
                || in_array($feature->getOptionId(), [
                    $optionFolder->getId(), $optionFolder2->getId()
                ])
            ) {
                throw new Siberian_Exception(__('You cannot add a Folder feature inside a folder!'));
            }

            $nextPositon = $feature->getNextFolderCategoryPosition($parentId);

            $feature
                ->setFolderId($optionValue->getId())
                ->setFolderCategoryPosition($nextPositon)
                ->setFolderCategoryId($parentId)
                ->save();

            $payload = [
                'success' => true,
                'position' => $nextPositon,
                'message' => __("Feature successfully added inside folder!")
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
     *
     */
    public function deleteAction()
    {
        $request = $this->getRequest();

        /**
         * @param $categoryId
         */
        function extractOptions($categoryId)
        {
            $options = (new Application_Model_Option_Value())
                ->findAll([
                    'folder_category_id = ?' => $categoryId
                ]);
            foreach ($options as $option) {
                $option
                    ->setFolderId(null)
                    ->setFolderCategoryId(null)
                    ->setFolderCategoryPosition(null)
                    ->save();
            }
        }

        /**
         * @param $parentId
         */
        function recursiveDelete($parentId)
        {
            // First extract options from folder
            extractOptions($parentId);
            // Find childrens and loop
            $childrens = (new Folder2_Model_Category())
                ->findAll([
                    'parent_id = ?' => $parentId
                ]);
            foreach ($childrens as $children) {
                // Recursively delete childrens
                recursiveDelete($children->getId());
                $children->delete();
            }
        }

        try {
            $categoryId = $request->getParam('categoryId');
            $category = (new Folder2_Model_Category())
                ->find($categoryId);

            recursiveDelete($categoryId);
            $category->delete();

            $payload = [
                'success' => true,
                'message' => __("The folder & it's sub-folders have been removed!")
            ];

        } catch (Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }

}
