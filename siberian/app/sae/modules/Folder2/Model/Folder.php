<?php

/**
 * Class Folder2_Model_Folder
 *
 * @method integer getId()
 * @method Folder2_Model_Db_Table_Folder getTable()
 * @method $this setRootCategoryId(integer $categoryId)
 */
class Folder2_Model_Folder extends Core_Model_Default {

    /**
     * @var array
     */
    public $cache_tags = [
        'feature_folder2',
    ];

    /**
     * @var bool
     */
    protected $_is_cacheable = true;

    /**
     * @var
     */
    protected $_root_category;

    /**
     * Folder2_Model_Folder constructor.
     * @param array $params
     */
    public function __construct($params = []) {
        parent::__construct($params);
        $this->_db_table = 'Folder2_Model_Db_Table_Folder';

        // Default to version 2!
        $this->setVersion(2);

        return $this;
    }

    /**
     * @return bool
     */
    public function getShowSearch() {
        return ($this->getData('show_search') === '1');
    }

    /**
     * @param $valueId
     * @return array
     */
    public function getInappStates($valueId) {
        $inAppStates = [
            [
                'state' => 'folder2-category-list',
                'offline' => true,
                'params' => [
                    'value_id' => $valueId,
                ],
            ]
        ];

        return $inAppStates;
    }

    /**
     * @param Application_Model_Option_Value $optionValue
     * @return array
     */
    public function getFeaturePaths($optionValue) {
        return [];
        /**if (!$this->isCacheable()) {
            return [];
        }

        $valueId = $optionValue->getId();
        $cacheId = "feature_paths_valueid_{$valueId}";
        if (!$result = $this->cache->load($cacheId)) {

            $paths = [];
            $paths[] = $optionValue->getPath('findall', [
                'value_id' => $optionValue->getId()
            ], false);

            $paths = array_merge($paths, $this->_get_subcategories_feature_paths($this->getRootCategory(), $optionValue));

            $this->cache->save($paths, $cacheId,
                $this->cache_tags + [
                    'feature_paths',
                    'feature_paths_valueid_' . $valueId
                ]);
        } else {
            $paths = $result;
        }

        return $paths;*/
    }

    /**
     * @param Application_Model_Option_Value $optionValue
     * @return array
     */
    public function getAssetsPaths($optionValue) {
        return [];
        /**if (!$this->isCacheable()) {
            return [];
        }

        $paths = [];

        $valueId = $optionValue->getId();
        $cacheId = 'assets_paths_valueid_' . $valueId;
        if (!$result = $this->cache->load($cacheId)) {

            $folder = $optionValue->getObject();

            if ($folder->getId()) {
                $category = new Folder2_Model_Category();
                $category->find($folder->getRootCategoryId(), 'category_id');
                if ($category->getId()) {
                    $paths[] = $category->getPictureUrl();
                    $paths = array_merge($paths, $this->_get_subcategories_assets_paths($category));
                }
            }

            $this->cache->save($paths, $cacheId,
                $this->cache_tags + [
                    'assets_paths',
                    'assets_paths_valueid_' . $valueId
                ]);
        } else {
            $paths = $result;
        }

        return $paths;*/
    }

    /**
     * @param Application_Model_Option_Value $optionValue
     * @return bool|array
     */
    public function getEmbedPayload($optionValue = null) {
        if (!$optionValue) {
            return false;
        }

        if ($this->getId()) {
            $categories = (new Folder2_Model_Category())
                ->findAll(
                    [
                        'value_id = ?' => $optionValue->getId()
                    ],
                    'pos ASC'
                );

            $collection = [];
            foreach ($categories as $category) {
                $url = __path('folder2/mobile_list', array(
                    'value_id' => $optionValue->getId(),
                    'category_id' => $category->getId()
                ));

                $collection[] = [
                    'title' => (string) $category->getTitle(),
                    'subtitle' => (string) $category->getSubtitle(),
                    'category_id' => (integer) $category->getCategoryId(),
                    'parent_id' => is_null($category->getParentId()) ? null : (integer) $category->getParentId(),
                    'type_id' => (string) $category->getTypeId(),
                    'picture' => (string) '/images/application' . $category->getPicture(),
                    'thumbnail' => (string) '/images/application' . $category->getThumbnail(),
                    'url' => $url,
                    'path' => $url,
                    'is_subfolder' => (boolean) $category->getParentId(),
                    'is_feature' => false
                ];
            }

            // Features assigned to the current optionValue
            $features = (new Application_Model_Option_Value())
                ->findAll(
                    [
                        'folder_id = ?' => $optionValue->getId()
                    ],
                    'folder_category_position ASC'
                );

            $color = $this->getApplication()
                ->getBlock('list_item')
                ->getImageColor();

            foreach ($features as $feature) {
                $hideNavbar = false;
                $useExternalApp = false;
                if ($objectLink = $feature->getObject()->getLink() AND is_object($objectLink)) {
                    $hideNavbar = $objectLink->getHideNavbar();
                    $useExternalApp = $objectLink->getUseExternalApp();
                }

                $url = $feature->getPath(null, [
                    'value_id' => $feature->getId()
                ], false);

                $pictureFile = null;
                if ($feature->getIconId()) {
                    $pictureFile = Core_Controller_Default_Abstract::sGetColorizedImage($feature->getIconId(), $color);
                }

                $collection[] = [
                    'title' => (string) $feature->getTabbarName(),
                    'subtitle' => (string) $feature->getTabbarSubtitle(),
                    'category_id' => null,
                    'parent_id' => (integer) $feature->getFolderCategoryId(),
                    'type_id' => 'feature',
                    'picture' => null,
                    'thumbnail' => $pictureFile,
                    'url' => $url,
                    'path' => $url,
                    'code' => $feature->getCode(),
                    'offline_mode' => (boolean) $feature->getObject()->isCacheable(),
                    'hide_navbar' => (boolean) $hideNavbar,
                    'use_external_app'  => (boolean) $useExternalApp,
                    'is_link' => !(boolean) $feature->getIsAjax(),
                    'has_parent_folder' => true,
                    'is_feature' => true,
                    'is_locked' => (boolean) $feature->isLocked(),
                ];
            }

            return [
                'showSearch' => (boolean) $this->getShowSearch(),
                'collection' => $collection
            ];
        }

        return [
            'error' => true
        ];
    }

    /**
     * @param $optionValue
     * @return $this
     */
    public function deleteFeature($optionValue) {

        if(!$this->getId()) {
            return $this;
        }

        $this->getRootCategory()->delete();

        return $this->delete();
    }

    /**
     * @return Folder2_Model_Category
     */
    public function getRootCategory() {
        if (!$this->_root_category) {
            $this->_root_category = (new Folder2_Model_Category())
                ->find($this->getRootCategoryId());
        }

        return $this->_root_category;
    }
}
