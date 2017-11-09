<?php

class Push_Form_Global extends Siberian_Form_Abstract {

    public function init() {

        parent::init();

        $this
            ->setAction(__url("push/admin/send"))
            ->setAttrib("id", "form-push-global");

        self::addClass("create", $this);

        $title = $this->addSimpleText("title", __("Title"));
        $title
            ->setRequired(true)
        ;

        $message = $this->addSimpleTextarea("message", __("Message"));
        $message
            ->setAttrib("MAXLENGTH", 220)
            ->setRequired(true)
            ->setNewDesignLarge()
        ;

        $send_to_all = $this->addSimpleCheckbox("send_to_all", __("Send to all applications"));

        $devices = $this->addSimpleSelect("devices", __("Target devices"), array(
            "all" => __("iOS & Android"),
            "ios" => __("iOS only"),
            "android" => __("Android only"),
        ));

        $open_url = $this->addSimpleCheckbox("open_url", __("Open custom URL"));

        $url = $this->addSimpleText("url", __("URL"));

        $checked = $this->addSimpleHidden("checked");

        $this->addNav("send_push", __("Send"), false, false);
    }

}