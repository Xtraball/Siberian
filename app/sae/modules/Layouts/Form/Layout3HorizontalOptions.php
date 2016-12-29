<?php

class Layouts_Form_Layout3HorizontalOptions extends Siberian_Form_Options_Abstract {


    public function init() {
        parent::init();

        /** Bind as a create form */
        self::addClass("create", $this);
        self::addClass("form-layout-options", $this);

        $title = $this->addSimpleCheckbox("colorizePager", __("Colorize pager"));

        $this->addSimpleHidden("app_id");

        $this->addNav("submit", __("Save"), false, false);

        self::addClass("btn-sm", $this->getDisplayGroup("submit")->getElement(__("Save")));

    }

    /**
     * @param array $data
     * @return bool
     */
    public function isValid($data) {

        $app_id = $data["app_id"];
        $css = Core_Model_Directory::getBasePathTo("var/cache/css/{$app_id}.css");
        if(file_exists($css)) {
            unlink($css);
        }

        return parent::isValid($data);
    }

}