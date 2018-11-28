<?php

/**
 * Class Places_Form_Settings
 */
class Places_Form_Settings extends Siberian_Form_Abstract
{
    /**
     * @throws Zend_Form_Exception
     */
    public function init()
    {
        parent::init();

        $this
            ->setAction(__path("/places/application/edit-settings"))
            ->setAttrib("id", "form-edit-settings");

        self::addClass('create', $this);

        $this->addSimpleHidden("value_id");

        $pages = [
            'places' => __("All places"),
            'categories' => __("Categories"),
        ];

        $defaultPage = $this->addSimpleSelect("default_page", __("Default page"), $pages);

        $layout = [
            'place-100' => __("List"),
            'place-50' => __("Two columns"),
            'place-33' => __("Three columns"),
        ];

        $defaultLayout = $this->addSimpleSelect("default_layout", __("Default layout"), $layout);

        $distance = [
            'km' => __("Kilometers"),
            'mi' => __("Miles"),
        ];

        $distanceUnit = $this->addSimpleSelect("distance_unit", __("Distance unit"), $distance);

        // Featured places are disabled for now.

        //$showFeatured = $this->addSimpleCheckbox("show_featured", __("Show featured labels"));
        //$featuredLabel = $this->addSimpleText("featured_label", __("Featured label"));

        //$showNonFeatured = $this->addSimpleCheckbox("show_non_featured", __("Show non-featured labels"));
        //$nonFeaturedLabel = $this->addSimpleText("non_featured_label", __("Non-featured label"));

        $submit = $this->addSubmit(__("Save"), __("Save"));
        $submit->addClass("pull-right");
    }
}