<?php

namespace Fanwall\Form;

use Siberian_Form_Abstract as FormAbstract;
/**
 * Class Settings
 * @package Fanwall\Form
 */
class Settings extends FormAbstract
{
    /**
     * @throws \Zend_Form_Exception
     */
    public function init()
    {
        parent::init();

        $this
            ->setAction(__path("/fanwall/application/edit-settings"))
            ->setAttrib("id", "form-fanwall-settings");
        /** Bind as a create form */
        self::addClass("create", $this);

        $this->addSimpleHidden("fanwall_id");

        $radius = $this->addSimpleText("radius", p__("fanwall","Near me radius (in km)"));
        $radius
            ->setRequired(true);

        $this->addSimpleSelect("design", p__("fanwall","Design"), [
            "list" => p__("fanwall", "List"),
            "card" => p__("fanwall", "Card"),
        ]);

        $icons = [
            "icon_topics" => "Topics",
            "icon_nearby" => "Nearby",
            "icon_map" => "Map",
            "icon_gallery" => "Gallery",
            "icon_post" => "Post",
        ];

        foreach ($icons as $column => $label) {
            $this->addSimpleImage($column, p__("fanwall", $label), p__("fanwall", $label), [
                "width" => 64,
                "height" => 64,
            ]);
        }

        $this->groupElements("group_icons", [
            "icon_topics_button",
            "icon_nearby_button",
            "icon_map_button",
            "icon_gallery_button",
            "icon_post_button",
        ], p__("fanwall", "Custom icons"));

        $valueId = $this->addSimpleHidden("value_id");
        $valueId
            ->setRequired(true);

        $submit = $this->addSubmit(p__("fanwall", "Save"), p__("fanwall", "Save"));
        $submit->addClass("pull-right");
    }

    /**
     * @param $fanwallId
     */
    public function setFanwallId($fanwallId)
    {
        $this
            ->getElement("fanwall_id")
            ->setValue($fanwallId)
            ->setRequired(true);
    }
}