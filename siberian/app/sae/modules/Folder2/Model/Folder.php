<?php

use Siberian\Json;

/**
 * Class Folder2_Model_Folder
 *
 * @method integer getId()
 * @method Folder2_Model_Db_Table_Folder getTable()
 * @method $this setRootCategoryId(integer $categoryId)
 */
class Folder2_Model_Folder extends Core_Model_Default {

    /**
     * Maximum nested level
     *
     * @var int
     */
    public static $maxNestedLevel = 12;

    /**
     * @var array
     */
    public $cache_tags = [
        'feature_folder2',
    ];

    /**
     * @var bool
     */
    protected $_is_cacheable = false;

    /**
     * @var
     */
    protected $_root_category;

    /**
     * @var string
     */
    protected $_db_table = Folder2_Model_Db_Table_Folder::class;

    /**
     * Folder2_Model_Folder constructor.
     * @param array $params
     */
    public function __construct($params = []) {
        parent::__construct($params);

        // Default to version 2!
        $this->setVersion(2);
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
    }

    /**
     * @param Application_Model_Option_Value $optionValue
     * @return array
     */
    public function getAssetsPaths($optionValue) {
        return [];
    }

    /**
     * @param null $optionValue
     * @return array|bool
     * @throws Zend_Exception
     */
    public function getEmbedPayload($optionValue = null) {
        if (!$optionValue) {
            return false;
        }
        $linkCodes = ['weblink_mono', 'prestashop', 'magento', 'volusion', 'woocommerce', 'shopify'];


        if ($this->getId()) {
            $categories = (new Folder2_Model_Category())
                ->findAll(
                    [
                        'value_id = ?' => $optionValue->getId()
                    ],
                    'pos ASC'
                );

            $indexCategories = [];
            $collection = [];
            foreach ($categories as $category) {
                $params = [
                    'value_id' => $optionValue->getId(),
                    'category_id' => $category->getId(),
                    'layout_id' => is_null($category->getLayoutId()) ? -1 : $category->getLayoutId()
                ];
                $url = __path('folder2/mobile_list', $params);

                $element = [
                    'title' => (string) $category->getTitle(),
                    'subtitle' => (string) $category->getSubtitle(),
                    'showCover' => (boolean) $category->getShowCover(),
                    'showTitle' => (boolean) $category->getShowTitle(),
                    'layout_id' => (integer) $category->getLayoutId(),
                    'category_id' => (integer) $category->getCategoryId(),
                    'parent_id' => is_null($category->getParentId()) ? null : (integer) $category->getParentId(),
                    'type_id' => (string) $category->getTypeId(),
                    'picture' => (string) '/images/application' . $category->getPicture(),
                    'thumbnail' => (string) '/images/application' . $category->getThumbnail(),
                    'icon_is_colorable' => false,
                    'url' => $url,
                    'path' => $url,
                    'lazy_load' => null,
                    'open_callback_class' => null,
                    'is_active' => true,
                    'is_visible' => true,
                    'is_locked' => false,
                    'is_subfolder' => (boolean) $category->getParentId(),
                    'is_feature' => false
                ];

                $collection[] = $element;

                $categoryId = (integer) $category->getCategoryId();
                $indexCategories[$categoryId] = $element;
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
                ->getBlock('tabbar')
                ->getImageColor();

            foreach ($features as $feature) {

                // Skip unpublished features.
                if (!$feature->isActive()) {
                    continue;
                }

                $hideNavbar = false;
                $useExternalApp = false;
                if ($objectLink = $feature->getObject()->getLink() AND is_object($objectLink)) {
                    $hideNavbar = $objectLink->getHideNavbar();
                    $useExternalApp = $objectLink->getUseExternalApp();
                }

                try {
                    $settings = Json::decode($feature->getSettings());
                } catch (\Exception $e) {
                    $settings = [];
                }

                // Special uri places
                $uris = $feature->getAppInitUris();

                $pictureFile = null;
                if ($feature->getIconId()) {
                    $pictureFile = Core_Controller_Default_Abstract::sGetColorizedImage($feature->getIconId(), $color);
                }

                $folderBlock = [
                    'title' => (string) $feature->getTabbarName(),
                    'subtitle' => (string) $feature->getTabbarSubtitle(),
                    'layout_id' => (integer) $feature->getLayoutId(),
                    'category_id' => null,
                    'parent_id' => (integer) $feature->getFolderCategoryId(),
                    'type_id' => 'feature',
                    'picture' => null,
                    'thumbnail' => $pictureFile,
                    'icon_is_colorable' => (boolean) $feature->getImage()->getCanBeColorized(),
                    'url' => $uris['featureUrl'],
                    'path' => $uris['featurePath'],
                    'code' => $feature->getCode(),
                    'offline_mode' => (boolean) $feature->getObject()->isCacheable(),
                    'hide_navbar' => (boolean) $hideNavbar,
                    'use_external_app'  => (boolean) $useExternalApp,
                    'is_link' => !(boolean) $feature->getIsAjax(),
                    'has_parent_folder' => true,
                    'is_feature' => true,
                    'settings' => $settings,
                    'lazy_load' => $feature->getLazyLoad(),
                    'open_callback_class' => $feature->getOpenCallbackClass(),
                    'is_active' => (boolean) $feature->isActive(),
                    'is_visible' => true,
                    'is_locked' => (boolean) $feature->isLocked(),
                    'value_id' => (integer) $feature->getId(),
                ];

                // 4.18.3 link special options!
                $object = $feature->getObject();
                if ($object->getLink() &&
                    in_array($folderBlock['code'], $linkCodes, false)) {

                    $objectLink = $object->getLink();

                    // post 4.18.3 options
                    $folderBlock['link_url'] = (string)$objectLink->getData('url');
                    $folderBlock['external_browser'] = (boolean)$objectLink->getExternalBrowser();
                    $folderBlock['options'] = $objectLink->getOptions();
                }

                $collection[] = $folderBlock;
            }

            // Build search index!
            $searchIndex = [];
            foreach ($collection as $item) {
                $parentId = $item['parent_id'];
                $directParent = $indexCategories[$parentId];
                // Predecessor building name!
                // The item ALWAYS have at least one parent (the root folder)
                $previousParentId = $parentId;
                $searchElements = [];
                $ariaTitle = [];
                $loopFailover = 0;
                while (array_key_exists($previousParentId, $indexCategories)) {
                    $loopFailover = $loopFailover + 1;
                    $historyParent = $indexCategories[$previousParentId];

                    $ariaTitle[] = $historyParent['title'];
                    $searchElements[] = $historyParent['title'] . ' ' . $historyParent['subtitle'];

                    $previousParentId = $historyParent['parent_id'];

                    // Always break if the failover is reached!
                    if ($loopFailover > self::$maxNestedLevel) {
                        break;
                    }
                }

                $ariaTitleShort = $item['title'];
                if (array_key_exists($parentId, $indexCategories)) {
                    $ariaTitleShort = $directParent['title'] . ' > ' . $item['title'];
                }

                $searchIndex[] = [
                    'feature' => $item,
                    'searchElements' => implode_polyfill(' ', $searchElements),
                    'ariaTitle' => implode_polyfill(' > ', $ariaTitle),
                    'ariaTitleShort' => $ariaTitleShort,
                    'directParent' => $directParent
                ];
            }

            return [
                'showSearch' => (boolean) $this->getShowSearch(),
                'allowLineReturn' => (boolean) $this->getAllowLineReturn(),
                'cardDesign' => (boolean) $this->getCardDesign(),
                'collection' => $collection,
                'searchIndex' => $searchIndex
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
        if (!$this->getId()) {
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
