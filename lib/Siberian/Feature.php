<?php

/**
 * Class Siberian_Feature
 *
 * @version 4.2.0
 *
 * Utility class to install/update modules & features
 *
 */

class Siberian_Feature
{

    /**
     * Utility method to install icons
     *
     * @param $name
     * @param $icons
     * @return array()
     */
    public static function installIcons($name, $icons = array()) {

        $library = new Media_Model_Library();
        $library
            ->setData(array(
                "name" => $name,
            ))
            ->insertOnce(array("name"));

        $icon_id = 0;
        foreach($icons as $key => $icon_path) {
            $data = array(
                'library_id'        => $library->getId(),
                'link'              => $icon_path,
                'can_be_colorized'  => 1
            );

            $image = new Media_Model_Library_Image();
            $image
                ->setData($data)
                ->insertOnce(array("library_id", "link"));

            if($key == 0) {
                $icon_id = $image->getId();
            }
        }

        return array(
            "icon_id" => $icon_id,
            "library_id" => $library->getId(),
        );
    }

    /**
     * @param $name
     */
    public static function removeIcons($name) {
        $library = new Media_Model_Library();
        $library = $library->find($name, "name");
        $library->delete();
    }

    /**
     * Install layouts if the Feature has multiple
     *
     * @param $option_id
     * @param array $layout_data
     */
    public static function installLayouts($option_id, $slug, $layout_data = array()) {

        $layouts = array();

        foreach($layout_data as $layout_code) {
            $layouts[] = array(
                "code" => $layout_code,
                "option_id" => $option_id,
                "name" => "Layout {$layout_code}",
                "preview" => "/customization/layout/{$slug}/layout-{$layout_code}.png",
                "position" => $layout_code
            );
        }

        foreach ($layouts as $data) {
            $application_option_layout = new Application_Model_Option_Layout();
            $application_option_layout
                ->setData($data)
                ->insertOnce(array("preview"));
        }
    }

    /**
     * Clean-up layouts
     *
     * @param $option_id
     * @param $slug
     * @param array $layout_data
     */
    public static function removeLayouts($option_id, $slug, $layout_data = array()) {
        $application_option_layout = new Application_Model_Option_Layout();

        foreach($layout_data as $layout_code) {
            $search  = "/customization/layout/{$slug}/layout-{$layout_code}.png";
            $layout = $application_option_layout->find(array("preview" => $search, "option_id" => $option_id));
            if($layout->getId()) {
                $layout->delete();
            }
        }

    }

    public static function installCategory($code, $name, $icon, $position = null) {
        /** @todo */
    }

    /**
     * @param $category_code
     * @param $data
     * @param array $keys
     * @return Application_Model_Option
     */
    public static function install($category_code, $data, $keys = array()) {

        $category = new Application_Model_Option_Category();
        $category
            ->find($category_code, "code");

        $data["category_id"] = $category->getId();

        /** Setting position if not. */
        if(!isset($data["position"])) {
            $amo = new Application_Model_Option();
            $db = $amo->getTable();
            $select = $db->select()
                ->order(array("position DESC"));

            $result = $db->fetchRow($select);
            if($result && $result->getPosition()) {
                $data["position"] = $result->getPosition() + 10;
            }
        }

        if(isset($data["custom_fields"]) && is_array($data["custom_fields"])) {
            $data["custom_fields"] = json_encode($data["custom_fields"]);
        }

        $option = new Application_Model_Option();
        $option
            ->setData($data)
            ->insertOrUpdate($keys);

        return $option;
    }

    /**
     * Create ACL for a given option, or manually with an array
     * @param Application_Model_Option|array $option
     */
    public static function installAcl($option) {

        $features_resources = array(
            "code" => "feature",
            "label" => "Features",
        );

        if(!is_array($option)) {
            $child_resource = array(
                "code"  => "feature_".$option->getCode(),
                "label" => $option->getName(),
                "url"   => $option->getDesktopUri()."*"
            );
        } else {
            $child_resource = $option;
        }

        // Create feature resource in case it doesn't exists
        $resource = new Acl_Model_Resource();
        $resource->setData($features_resources)
            ->insertOrUpdate(array("code"));

        $parent_id = $child_resource["parent_id"];

        if(empty($parent_id)) {
            $child_resource["parent_id"] = $resource->getId();
        } elseif(!is_numeric($parent_id)) { // If parent_id is not numeric, search existing ACL using code
            $tmp_res = new Acl_Model_Resource();
            $tmp_res->find($parent_id, "code");
            $tmp_res_id = $tmp_res->getId();
            if(empty($tmp_res_id)) {
                $tmp_res->find("feature_".$parent_id, "code");
                if(empty($tmp_res_id))
                    throw new ErrorException("Cannot find Acl Resource with code: ".$parent_id." or feature_".$parent_id);
            }
            $child_resource["parent_id"] = $tmp_res->getId();
        } elseif (is_numeric($parent_id)) {
            $child_resource["parent_id"] = intval($parent_id, 10);
        }

        $child = new Acl_Model_Resource();
        $child->setData($child_resource)
            ->insertOrUpdate(array("code"));

        if(!empty($child_resource["children"])) {
            foreach($child_resource["children"] as $child_child_resource) {
                $child_child_resource["parent_id"] = $child->getId();
                self::installAcl($child_child_resource);
            }
        }
    }

    /**
     * @param $code
     */
    public static function uninstallFeature($code) {
        $option = new Application_Model_Option();
        $option->find($code, "code");
        $option->delete();
    }

    /**
     * @param $code
     */
    public static function uninstallModule($name) {
        $module = new Installer_Model_Installer_Module();
        $module->find($name, "name");
        $module->delete();
    }

    /**
     * @param array $tables
     */
    public static function dropTables($tables = array()) {
        $db = Zend_Db_Table::getDefaultAdapter();
        $db->query("SET FOREIGN_KEY_CHECKS = 0;");
        foreach($tables as $table) {
            $statement = $db->prepare("DROP TABLE ?;");
            $statement->execute(array($table));
        }
        $db->query("SET FOREIGN_KEY_CHECKS = 1;");
    }

    /**
     * @param Application_Model_Option_Value $option_value
     * @param $tmp_path
     * @return null|string
     * @throws exception
     */
    public static function moveUploadedFile(Application_Model_Option_Value $option_value, $tmp_path) {
        $path = null;

        $filename = pathinfo($tmp_path, PATHINFO_BASENAME);
        $relative_path = $option_value->getImagePathTo();
        $folder = Application_Model_Application::getBaseImagePath().$relative_path;
        $img_dst = $folder.'/'.$filename;
        $img_src = Core_Model_Directory::getTmpDirectory(true).'/'.$filename;

        if(!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }

        if(!copy($img_src, $img_dst)) {
            throw new exception(__("An error occurred while saving your picture. Please try again later."));
        } else {
            $path = $relative_path.'/'.$filename;
        }

        return $path;
    }

    /**
     * Installing a cronjob, defaults to every 5 minutes, active, low priority.
     *
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
     */
    public static function installCronjob($name, $command, $minute = 5, $hour = -1, $month_day = -1, $month = -1, $week_day = -1, $is_active = true, $priority = 5, $standalone = false) {
        $job = new Cron_Model_Cron();
        $job->setData(array(
            "name"          => $name,
            "command"       => $command,
            "minute"        => $minute,
            "hour"          => $hour,
            "month_day"     => $month_day,
            "month"         => $month,
            "week_day"      => $week_day,
            "is_active"     => $is_active,
            "priority"      => $priority,
            "standalone"    => $standalone,
        ));

        $job->insertOrUpdate(array("command"));
    }

}
