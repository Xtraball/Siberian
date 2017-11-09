<?php

class Front_View_Default extends Core_View_Default {

    protected static $_current_white_label_editor;

    public function getCurrentWhiteLabelEditor() {
        return self::$_current_white_label_editor;
    }

    public static function setCurrentWhiteLabelEditor($backoffice) {
        self::$_current_white_label_editor = $backoffice;
    }

}