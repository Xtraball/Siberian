<?php

/**
 * Class Translation_Backoffice_ListController
 */
class Translation_Backoffice_ListController extends Backoffice_Controller_Default {
    /**
     *
     */
    public function loadAction() {
        $payload = [
            "title" => sprintf("%s > %s",
                __("Settings"),
                __("Translations")),
            "icon" => "fa-language",
        ];

        $this->_sendJson($payload);
    }

    /**
     *
     */
    public function findallAction() {
        $payload = [];

        foreach (Core_Model_Language::getManageable() as $row) {
            $payload[] = [
                "id" => base64_encode($row["code"]),
                "code" => $row["code"],
                "name" => $row["name"],
                "enabled" => $row["enabled"],
                "toggle" => $row["toggle"],
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     * Switch a shipped locale on or off
     */
    public function toggleAction() {
        try {
            if (__getConfig("is_demo")) {
                throw new \Siberian\Exception(__("You cannot change translation, this is a demo version."));
            }

            $data = $this->getRequest()->getBodyParams();
            $code = (string) ($data["code"] ?? "");
            $enabled = filter_var($data["enabled"] ?? false, FILTER_VALIDATE_BOOLEAN);

            Core_Model_Language::toggleLocale($code, $enabled);

            $payload = [
                "success" => true,
                "message" => p__("translation", "Language saved."),
            ];
        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }
}
