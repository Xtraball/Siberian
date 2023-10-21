<?php

namespace Siberian;

/**
 * Class \Siberian\Feature
 *
 * @version 4.20.13
 *
 * Utility class to install/update modules & features
 *
 */

class Feature
{
    const PATH_ASSETS = '/images/assets';

    /**
     * @var array
     */
    public static $custom_ratios = [];

    /**
     * @var array
     */
    public static $layout_options = [];

    /**
     * @var array
     */
    public static $layoutDataProcessor = [];

    /**
     * Utility method to install icons
     *
     * @param $name
     * @param $icons
     * @param $canBeColorized
     * @return array()
     */
    public static function installIcons($name, $icons = [], $canBeColorized = true): array
    {
        $library = (new \Media_Model_Library())->find($name, 'name');
        if (!$library || !$library->getId()) {
            $library
                ->setName($name)
                ->save();
        }
        $libraryId = $library->getId();

        // When we have multiple icons for a library, we use the first as default
        $iconId = null;

        foreach ($icons as $key => $iconPath) {
            $keywords = '';
            if (is_array($iconPath)) {
                if (array_key_exists('colorize', $iconPath)) {
                    $canBeColorized = filter_var($iconPath['colorize'], FILTER_VALIDATE_BOOLEAN);
                }
                if (array_key_exists('path', $iconPath)) {
                    $iconPath = $iconPath['path'];
                }
                // Siberian 4.20.17+ support for keywords
                if (is_array($iconPath) && array_key_exists('keywords', $iconPath)) {
                    $keywords = $iconPath['keywords'];
                }
            }

            $data = [
                'library_id' => $libraryId,
                'link' => $iconPath,
                'keywords' => $keywords,
                'can_be_colorized' => $canBeColorized
            ];

            $image = (new \Media_Model_Library_Image())->find([
                'library_id = ?' => $libraryId,
                'link = ?' => $iconPath,
            ]);
            if (!$image || !$image->getId()) {
                $image
                    ->setData($data)
                    ->save();
            } else {
                $image
                    ->setKeywords($keywords)
                    ->setCanBeColorized($canBeColorized)
                    ->save();
            }

            $goodImageId = $image->getId();
            if ($iconId === null) {
                $iconId = $goodImageId;
            }
        }

        return [
            'icon_id' => $iconId,
            'library_id' => $library->getId(),
        ];
    }

    /**
     * @param $name
     */
    public static function removeIcons($name)
    {
        $library = new \Media_Model_Library();
        $library = $library->find($name, "name");
        $library->delete();
    }

    /**
     * @param $datas
     * @param string $category_code
     * @throws \Zend_Exception
     */
    public static function installApplicationLayout($datas, $category_code = "custom")
    {
        $category_model = new \Application_Model_Layout_Category();
        $category = $category_model->find($category_code, "code");

        if (empty($datas["category_id"])) {
            $datas["category_id"] = $category->getId();
        }

        $layout = new \Application_Model_Layout_Homepage();
        $layout
            ->setData($datas)
            ->insertOrUpdate(["code"]);

        // Create or update
        $designResource = (new \Acl_Model_Resource())->find('editor_design_layout', 'code');
        if ($designResource && $designResource->getId()) {
            // When done, create it's ACL
            $code = $layout->getCode();
            $name = $layout->getName();
            $resource = new \Acl_Model_Resource();
            $resource
                ->setData(
                    [
                        'parent_id' => $designResource->getId(),
                        'code' => 'layout_' . $code,
                        'label' => $name,
                    ]
                )
                ->insertOrUpdate(['code']);
        }
    }

    /**
     * Install layouts if the Feature has multiple
     *
     * @param $option_id
     * @param array $layout_data
     */
    public static function installLayouts($option_id, $slug, $layout_data = [])
    {

        $layouts = [];

        foreach ($layout_data as $layout_code) {
            $layouts[] = [
                "code" => $layout_code,
                "option_id" => $option_id,
                "name" => "Layout {$layout_code}",
                "preview" => "/customization/layout/{$slug}/layout-{$layout_code}.png",
                "position" => $layout_code
            ];
        }

        foreach ($layouts as $data) {
            $application_option_layout = new \Application_Model_Option_Layout();
            $application_option_layout
                ->setData($data)
                ->insertOnce(["preview", "option_id"]);
        }
    }

    /**
     * Clean-up layouts
     *
     * @param $option_id
     * @param $slug
     * @param array $layout_data
     */
    public static function removeLayouts($option_id, $slug, $layout_data = [])
    {
        $application_option_layout = new \Application_Model_Option_Layout();

        foreach ($layout_data as $layout_code) {
            $search = "/customization/layout/{$slug}/layout-{$layout_code}.png";
            $layout = $application_option_layout->find(["preview" => $search, "option_id" => $option_id]);
            if ($layout->getId()) {
                $layout->delete();
            }
        }

    }

    /**
     * @param $code
     * @param $name
     * @param $icon
     * @param null $position
     */
    public static function installCategory($code, $name, $icon, $position = null)
    {
        /** @todo */
    }

    /**
     * @param $category
     * @param $feature_data
     * @param $icons
     * @return \Application_Model_Option
     * @throws \ErrorException
     * @throws \Zend_Exception
     */
    public static function installFeature($category, $feature_data, $icons)
    {
        $name = $feature_data["name"];
        $feature_icons = self::installIcons($name . "-flat", $icons);
        $feature_data["library_id"] = $feature_icons["library_id"];
        $feature_data["icon_id"] = $feature_icons["icon_id"];

        $option = self::install($category, $feature_data, ['code']);
        self::installAcl($option);

        Assets::copyAssets("/app/local/modules/resources/var/apps/");

        return $option;
    }

    /**
     * @param $category_code
     * @param $data
     * @param array $keys
     * @return \Application_Model_Option
     * @throws \Zend_Exception
     */
    public static function install($category_code, $data, $keys = [])
    {

        $category = new \Application_Model_Option_Category();
        $category
            ->find($category_code, "code");

        $data["category_id"] = $category->getId();

        // Application!
        if (!isset($data["position"])) {
            $amo = new \Application_Model_Option();
            $db = $amo->getTable();
            $select = $db->select()
                ->order(["position DESC"]);

            $result = $db->fetchRow($select);
            if ($result && $result->getPosition()) {
                $data["position"] = $result->getPosition() + 10;
            }
        }

        if (isset($data["custom_fields"]) && is_array($data["custom_fields"])) {
            $data["custom_fields"] = json_encode($data["custom_fields"]);
        }

        $option = new \Application_Model_Option();
        $option
            ->setData($data)
            ->insertOrUpdate($keys);

        return $option;
    }

    /**
     * @param $option
     * @throws \ErrorException
     */
    public static function installAcl($option)
    {

        $features_resources = [
            "code" => "feature",
            "label" => "Features",
        ];

        if (!is_array($option)) {
            $child_resource = [
                "code" => "feature_" . $option->getCode(),
                "label" => $option->getData("name"),
                "url" => $option->getDesktopUri() . "*"
            ];
        } else {
            $child_resource = $option;
        }

        // Create feature resource in case it doesn't exists
        $resource = new \Acl_Model_Resource();
        $resource->setData($features_resources)
            ->insertOrUpdate(["code"]);

        $parent_id = $child_resource['parent_id'] ?? null;

        if (empty($parent_id)) {
            $child_resource["parent_id"] = $resource->getId();
        } elseif (!is_numeric($parent_id)) { // If parent_id is not numeric, search existing ACL using code
            $tmp_res = new \Acl_Model_Resource();
            $tmp_res->find($parent_id, "code");
            $tmp_res_id = $tmp_res->getId();
            if (empty($tmp_res_id)) {
                $tmp_res->find("feature_" . $parent_id, "code");
                if (empty($tmp_res_id))
                    throw new \ErrorException("Cannot find Acl Resource with code: " . $parent_id . " or feature_" . $parent_id);
            }
            $child_resource["parent_id"] = $tmp_res->getId();
        } elseif (is_numeric($parent_id)) {
            $child_resource["parent_id"] = intval($parent_id, 10);
        }

        $child = new \Acl_Model_Resource();
        $child->setData($child_resource)
            ->insertOrUpdate(["code"]);

        if (!empty($child_resource["children"])) {
            foreach ($child_resource["children"] as $child_child_resource) {
                $child_child_resource["parent_id"] = $child->getId();
                self::installAcl($child_child_resource);
            }
        }
    }

    /**
     * @param $code
     */
    public static function uninstallFeature($code)
    {
        $option = new \Application_Model_Option();
        $option->find($code, "code");
        $option->delete();
    }

    /**
     * @param $name
     * @throws \Zend_Exception
     */
    public static function uninstallModule($name)
    {
        $module = new \Installer_Model_Installer_Module();
        $module->find($name, "name");
        $module->delete();
    }

    /**
     * @param array $tables
     */
    public static function dropTables($tables = [])
    {
        $db = \Zend_Db_Table::getDefaultAdapter();
        $db->query("SET FOREIGN_KEY_CHECKS = 0;");
        foreach ($tables as $table) {
            $statement = $db->prepare("DROP TABLE ?;");
            $statement->execute([$table]);
        }
        $db->query("SET FOREIGN_KEY_CHECKS = 1;");
    }

    /**
     * @param \Application_Model_Option_Value $option_value
     * @param $tmp_path
     * @return null|string
     * @throws Exception
     */
    public static function moveUploadedFile(\Application_Model_Option_Value $option_value, $tmp_path)
    {
        $path = null;

        $filename = pathinfo($tmp_path, PATHINFO_BASENAME);
        $relative_path = $option_value->getImagePathTo();
        $folder = \Application_Model_Application::getBaseImagePath() . $relative_path;
        $img_dst = $folder . '/' . $filename;
        $img_src = \Core_Model_Directory::getTmpDirectory(true) . '/' . $filename;

        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }

        if (!file_exists($img_dst) && !copy($img_src, $img_dst)) {
            throw new Exception('#343-01: ' .
                __('An error occurred while saving your picture. Please try again later.'));
        }

        $path = $relative_path . '/' . $filename;
        unlink($img_src);

        return $path;
    }

    /**
     * @param \Application_Model_Option_Value $optionValue
     * @param $content
     * @param null $filename
     * @return string
     * @throws Exception
     */
    public static function createFile(\Application_Model_Option_Value $optionValue, $content, $filename = null)
    {
        $path = null;

        $filename = is_null($filename) ? uniqid() : $filename;
        $relativePath = $optionValue->getImagePathTo();
        $folder = \Application_Model_Application::getBaseImagePath() . $relativePath;
        $imgDst = $folder . '/' . $filename;

        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }

        if (file_exists($imgDst)) {
            throw new Exception('#343-54: ' . __('An error occurred while saving your picture. Please try again later.'));
        } else {
            File::putContents($imgDst, $content);
        }

        return $relativePath . '/' . $filename;
    }

    /**
     * @param $app_id
     * @param $tmp_path
     * @return null|string
     * @throws Exception
     */
    public static function moveUploadedIcon($app_id, $tmp_path)
    {
        $path = null;

        $filename = pathinfo($tmp_path, PATHINFO_BASENAME);
        $relative_path = sprintf('/%s/icons/', $app_id);
        $folder = \Application_Model_Application::getBaseImagePath() . $relative_path;
        $img_dst = $folder . '/' . $filename;
        $img_src = tmp(true) . '/' . $filename;

        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }

        if (!copy($img_src, $img_dst)) {
            throw new Exception('#343-02: ' . __('An error occurred while saving your picture. Please try again later.'));
        } else {
            $path = $relative_path . '/' . $filename;
        }

        return $path;
    }

    /**
     * @param $tmpPath
     * @return string
     * @throws Exception
     */
    public static function moveAsset($tmpPath)
    {
        $filename = pathinfo($tmpPath, PATHINFO_BASENAME);

        // Create a folder for each year-month to spread assets accross time
        $monthFolder = sprintf("%s/%s", self::PATH_ASSETS, date('Y-m'));
        $monthFolderAbs = path($monthFolder);
        if (!is_dir($monthFolderAbs)) {
            mkdir($monthFolderAbs, 0777, true);
        }

        $destination = sprintf("%s/%s", $monthFolderAbs, $filename);
        $source = sprintf("%s/%s", tmp(true), $filename);

        if (!copy($source, $destination)) {
            throw new Exception('#343-20: ' .
                __('An error occurred while saving your picture. Please try again later.'));
        }

        return sprintf("%s/%s", $monthFolder, $filename);
    }

    /**
     * @param $option_value
     * @param $image
     * @return null|string
     * @throws Exception
     */
    public static function saveImageForOptionDelete($option_value, $image)
    {
        # If the file already exists in images/application
        if ($image === "_delete_") {
            $image_path = '';
        } else if (file_exists(path("images/application{$image}"))) {
            $image_path = $image;
        } else {
            $image_path = self::moveUploadedFile($option_value, $image);
        }

        return $image_path;
    }

    /**
     * @param $optionValue
     * @param $object
     * @param $params
     * @param $key
     * @param bool $delete
     * @throws exception
     */
    public static function formImageForOption($optionValue, $object, $params, $key, $delete = true)
    {
        if ($delete && ($params[$key] === "_delete_")) {
            $object->setData($key, '');
        } else if (file_exists(path("images/application{$params[$key]}"))) {
            // Nothing changed, skip!
        } else {
            $background = self::moveUploadedFile(
                $optionValue,
                tmp() . '/' . $params[$key]);
            $object->setData($key, $background);
        }
    }

    /**
     * @param $option_value
     * @param $image
     * @return null|string
     * @throws Exception
     */
    public static function saveImageForOption($option_value, $image)
    {
        # If the file already exists in images/application
        if (file_exists(path('images/application' . $image))) {
            # Nothing changed, skip
            $image_path = $image;
        } else {
            $image_path = self::moveUploadedFile($option_value, $image);
        }

        return $image_path;
    }

    /**
     * @param $option_value
     * @param $file
     * @return null|string
     * @throws Exception
     */
    public static function saveFileForOption($option_value, $file)
    {
        # If the file already exists in images/application
        if (file_exists(path('images/application' . $file))) {
            # Nothing changed, skip
            $file_path = $file;
        } else {
            $file_path = self::moveUploadedFile($option_value, $file);
        }

        return $file_path;
    }

    /**
     * @param $name
     * @param $command
     * @param int $minute
     * @param int $hour
     * @param int $month_day
     * @param int $month
     * @param int $week_day
     * @param bool $is_active
     * @param int $priority
     * @param bool $standalone
     * @param null $module_id
     * @throws \Zend_Exception
     */
    public static function installCronjob($name, $command, $minute = 5, $hour = -1, $month_day = -1, $month = -1,
                                          $week_day = -1, $is_active = true, $priority = 5, $standalone = false,
                                          $module_id = null)
    {
        $job = new \Cron_Model_Cron();
        $job->setData([
            "name" => $name,
            "command" => $command,
            "minute" => $minute,
            "hour" => $hour,
            "month_day" => $month_day,
            "month" => $month,
            "week_day" => $week_day,
            "is_active" => $is_active,
            "priority" => $priority,
            "standalone" => $standalone,
            "module_id" => $module_id
        ]);

        $job->insertOrUpdate(['command']);
    }

    /**
     * @param $command
     */
    public static function removeCronjob($command)
    {
        $job = new \Cron_Model_Cron();
        $job->find($command, "command");
        $job->delete();
    }

    /**
     * @param $array
     * @return mixed
     */
    public static function export_filter($array)
    {
        array_walk_recursive($array, function (&$item, $key) {
            if ($key === "id") {
                $item = false;
            }
        });

        $array = array_map("array_filter", $array);

        return $array;
    }

    /**
     * @param $layout_code
     * @param $callback
     */
    public static function registerRatioCallback($layout_code, $callback)
    {
        if (!isset(self::$custom_ratios[$layout_code])) {
            self::$custom_ratios[$layout_code] = $callback;
        }
    }

    /**
     * @param $layout_code
     * @return bool|mixed
     */
    public static function getRatioCallback($layout_code)
    {
        if (isset(self::$custom_ratios[$layout_code])) {
            return self::$custom_ratios[$layout_code];
        }

        return false;
    }

    /**
     * @param $layout_code
     * @param $form
     * @param $callback
     */
    public static function registerLayoutOptionsCallbacks($layout_code, $form, $callback)
    {
        if (!isset(self::$layout_options[$layout_code])) {
            self::$layout_options[$layout_code] = [
                "form" => $form,
                "callback" => $callback,
            ];
        }
    }

    /**
     * @param $layout_code
     * @return bool|mixed
     */
    public static function getLayoutOptionsCallbacks($layout_code)
    {
        if (isset(self::$layout_options[$layout_code])) {
            return self::$layout_options[$layout_code];
        }

        return false;
    }

    /**
     * @param $layoutCode
     * @param $callback
     */
    public static function registerDataProcessorForLayout($layoutCode, $callback)
    {
        if (!isset(self::$layoutDataProcessor[$layoutCode])) {
            self::$layoutDataProcessor[$layoutCode] = $callback;
        }
    }

    /**
     * @param $layoutCode
     * @param $data
     * @param $application
     * @return mixed
     */
    public static function processDataForLayout($layoutCode, $data, $application)
    {
        if (isset(self::$layoutDataProcessor[$layoutCode])) {
            return call_user_func_array(self::$layoutDataProcessor[$layoutCode], [$data, $application]);
        }
        return $data;
    }

}
