<?php

/**
 * Class Front_View_Default
 */
class Front_View_Default extends Core_View_Default
{

    /**
     * @var
     */
    protected static $_current_white_label_editor;

    /**
     * @return mixed
     */
    public function getCurrentWhiteLabelEditor()
    {
        return self::$_current_white_label_editor;
    }

    /**
     * @param $backoffice
     */
    public static function setCurrentWhiteLabelEditor($backoffice)
    {
        self::$_current_white_label_editor = $backoffice;
    }

}