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

        $option = new Application_Model_Option();
        $option
            ->setData($data)
            ->insertOrUpdate($keys);

        return $option;
    }

}
