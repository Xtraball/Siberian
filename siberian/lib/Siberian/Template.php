<?php

/**
 * Template tools
 *
 * @version 4.20.11
 * @author Xtraball SAS <dev@xtraball.com>
 */

namespace Siberian;

/**
 * Class Template
 * @package Siberian
 */
class Template
{
    /**
     * @param $moduleName
     * @param $name
     * @param $code
     * @param $layoutCode
     * @param array $categories
     * @param array $colors
     * @param array $options
     * @param int $position
     * @return mixed
     * @throws \Zend_Db_Statement_Exception
     * @throws \Zend_Exception
     * @throws \Zend_Validate_Exception
     */
    public static function installOrUpdate($moduleName,
                                           $name,
                                           $code,
                                           $layoutCode,
                                           $categories = ['Default'],
                                           $colors = [],
                                           $options = [],
                                           $position = 1000)
    {
        // Categories!
        self::categories($categories);

        // Template design!
        $templateDesign = self::design($moduleName, $name, $code, $layoutCode);

        // Ionic colors!
        if ($templateDesign->getId()) {
            self::ionicColors($colors, $templateDesign);

            if (!empty($categories)) {
                self::linkTemplateAndCategories($categories, $code);
            }

            self::features($templateDesign, $options);
        }

        $templateDesign
            ->setPosition($position)
            ->save();

        // Create or update
        $designResource = (new \Acl_Model_Resource())->find('editor_design_template', 'code');
        if ($designResource && $designResource->getId()) {
            // When done, create it's ACL
            $code = $templateDesign->getCode();
            $name = $templateDesign->getName();
            $resource = new \Acl_Model_Resource();
            $resource
                ->setData(
                    [
                        'parent_id' => $designResource->getId(),
                        'code' => 'template_' . $code,
                        'label' => $name,
                    ]
                )
                ->insertOrUpdate(['code']);
        }

        return $templateDesign;
    }

    /**
     * @param array $categories
     */
    public static function categories ($categories = ['Default'])
    {
        // Create new categories
        foreach ($categories as $categoryName) {
            $categoryData = [
                'original_name' => $categoryName,
                'code' => preg_replace('/[&\s]+/', "_", strtolower($categoryName))
            ];

            $categoryModel = (new \Template_Model_Category())
                ->find($categoryData['code'], 'code');

            if ($categoryModel->getId()) {
                $name = trim($categoryModel->getName());
                if (empty($name)) {
                    $categoryModel->setName($categoryName);
                }
            }

            $categoryModel
                ->setOriginalName($categoryData['original_name'])
                ->setCode($categoryData['code'])
                ->save();
        }
    }

    /**
     * @param $moduleName
     * @param $name
     * @param $code
     * @param $layoutCode
     * @return mixed
     * @throws Exception
     * @throws \Zend_Exception
     */
    public static function design ($moduleName, $name, $code, $layoutCode)
    {
        // Searching for the layout!
        $layout = (new \Application_Model_Layout_Homepage())
            ->find($layoutCode, 'code');

        if (!$layout && !$layout->getId()) {
            $layout = (new \Application_Model_Layout_Homepage())
                ->find('layout_6', 'code');

            if (!$layout && !$layout->getId()) {
                throw new Exception(p__('backoffice', 'The layout with code %s does not exists.'), $layoutCode);
            }
        }

        // Module path
        $frontController = \Zend_Controller_Front::getInstance();
        $moduleDirectory = $frontController->getModuleDirectory($moduleName);
        // Replace base
        $base = \Core_Model_Directory::getBasePathTo('/');
        $moduleBase = str_replace($base, '/', $moduleDirectory);

        // Values for the Template, icon, homepage, startup, etc ...
        $designCodes = [
            'name' => $name,
            'version' => '2',
            'layout_id' => $layout->getId(),
            'overview_new' =>
                sprintf('%s/resources/images/templates/%s/unified/overview_new.jpg',
                    $moduleBase,
                    $code),
            'icon' => sprintf('/../..%s/resources/images/templates/%s/unified/icon.jpg',
                $moduleBase,
                $code),
            'background_image_unified' =>
                sprintf('%s/resources/images/templates/%s/unified/background.jpg',
                    $moduleBase,
                    $code),
            'startup_image_unified' =>
                sprintf('%s/resources/images/templates/%s/unified/background.jpg',
                    $moduleBase,
                    $code),
        ];

        $design = (new \Template_Model_Design())
            ->find($code, 'code');

        $design
            ->setData($designCodes)
            ->setCode($code)
            ->save();

        return $design;
    }

    /**
     * @param $colors
     * @param $design
     * @throws \Zend_Exception
     * @throws \Zend_Validate_Exception
     */
    public static function ionicColors($colors, $design)
    {
        $blockIds = [];
        $blocks = (new \Template_Model_Block())
            ->findAll([
                'type_id = ?' => 3
            ]);

        foreach ($blocks as $block) {
            $blockIds[$block->getCode()] = $block->getId();
            foreach ($block->getChildren() as $child) {
                $blockIds[$child->getCode()] = $child->getId();
            }
        }

        foreach ($colors as $blockCode => $blockData) {
            $designBlock = (new \Template_Model_Design_Block())
                ->find([
                    'block_id' => $blockIds[$blockCode],
                    'design_id' => $design->getId()
                ]);

            // Skips missing codes!
            if (!array_key_exists($blockCode, $blockIds)) {
                continue;
            }

            $designBlock
                ->addData($blockData)
                ->setDesignId($design->getId())
                ->setBlockId($blockIds[$blockCode])
                ->save();
        }
    }

    /**
     * @param $categories
     * @param $code
     * @throws \Zend_Exception
     */
    public static function linkTemplateAndCategories ($categories, $code)
    {
        // Listing design ids!
        $designIds = [];
        $designs = (new \Template_Model_Design())
            ->findAll();
        foreach ($designs as $design) {
            $designIds[$design->getCode()] = $design->getId();
        }

        // Listing category ids!
        $categoryIds = [];
        $allCategories = (new \Template_Model_Category())->findAll();
        foreach ($allCategories as $category) {
            $categoryIds[$category->getCode()] = $category->getId();
        }

        // Clear all categories!
        $previousCategories = (new \Template_Model_Design_Category())
            ->findAll([
                'design_id = ?' => $designIds[$code]
            ]);
        foreach ($previousCategories as $previousCategory) {
            $previousCategory->delete();
        }

        foreach ($categories as $categoryName) {
            $categoryCode = preg_replace('/[&\s]+/', '_', strtolower($categoryName));

            $categoryDesign = (new \Template_Model_Design_Category())
                ->find([
                    'category_id' => $categoryIds[$categoryCode],
                    'design_id' => $designIds[$code]
                ]);

            $categoryDesign
                ->setCategoryId($categoryIds[$categoryCode])
                ->setDesignId($designIds[$code])
                ->insertOrUpdate(['category_id', 'design_id']);
        }
    }

    /**
     * @param $design
     * @throws \Zend_Db_Profiler_Exception
     * @throws \Zend_Db_Statement_Exception
     */
    public static function clearDesign($design)
    {
        // clear old options!
        $db = \Zend_Db_Table::getDefaultAdapter();
        $db->query('DELETE FROM template_design_content WHERE design_id = ' . $design->getId());
    }

    /**
     * @param $design
     * @param $options
     * @throws \Zend_Db_Statement_Exception
     * @throws \Zend_Exception
     */
    public static function features($design, $options)
    {
        // Ensure old features & icons are cleared!
        self::clearDesign($design);

        // Add feaetures
        self::addFeatures($design, $options);
    }

    /**
     * @param $design
     * @param $options
     * @throws \Zend_Exception
     */
    public static function addFeatures($design, $options)
    {
        foreach ($options as $code => $optionData) {

            // To allow for multiple times the same feature, we must get rid of the "code" as array key,
            // but require a fallback!
            $optionCode = $code;
            if (array_key_exists('code', $optionData)) {
                $optionCode = $optionData['code'];
            }

            $option = (new \Application_Model_Option())
                ->find($optionCode, 'code');

            // Just skip missing features!
            if (!$option->getId()) {
                continue;
            }

            $iconId = array_key_exists('icon_id', $optionData) ?
                (string) $optionData['icon_id'] : null;
            $iconLink = $optionData['icon'];
            $name = array_key_exists('name', $optionData) ?
                (string) $optionData['name'] : null;

            $backgroundImage = array_key_exists('background_image', $optionData) ?
                (string) $optionData['background_image'] : null;

            $colorized = array_key_exists('colorized', $optionData) ?
                (boolean) $optionData['colorized'] : true;

            if (isset($iconLink) &&
                is_null($iconId)) {
                $icon = (new \Media_Model_Library_Image());
                $icon
                    ->setLibraryId($option->getLibraryId())
                    ->setLink($iconLink)
                    ->setOptionId($option->getId())
                    ->setCanBeColorized($colorized)
                    ->setPosition(0)
                    ->save();

                $iconId = $icon->getId();
            }

            $designData = [
                'design_id' => $design->getId(),
                'option_id' => $option->getId(),
                'option_tabbar_name' => $name,
                'option_icon' => $iconId,
                'option_background_image' => $backgroundImage,
            ];

            $designContent = (new \Template_Model_Design_Content());
            $designContent
                ->setData($designData)
                ->save();
        }
    }
}
