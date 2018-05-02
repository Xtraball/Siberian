<?php

namespace AdminGenerator;

/**
 * Class Render
 */
class Render
{
    /**
     * @param $name
     * @param bool $required
     * @return string
     */
    public static function formBoolean($name, $required = false) {
        $code = '
        $'.$name.' = $this->addSimpleCheckbox("'.$name.'", __("'.self::humanize($name).'"));';

        if($required) {
            $code .= '
        $'.$name.'->setRequired(true);';
        }

        return $code;
    }

    /**
     * @param $name
     * @param bool $required
     */
    public static function formNumeric($name, $required = false) {
        $code = '
        $'.$name.' = $this->addSimpleText("'.$name.'", __("'.self::humanize($name).'"));';

        if($required) {
            $code .= '
        $'.$name.'->setRequired(true);';
        }

        return $code;
    }

    /**
     * @param $name
     * @param bool $required
     * @return string
     */
    public static function formText($name, $required = false) {
        $code = '
        $'.$name.' = $this->addSimpleText("'.$name.'", __("'.self::humanize($name).'"));';

        if($required) {
            $code .= '
        $'.$name.'->setRequired(true);';
        }

        return $code;
    }

    /**
     * @param $name
     * @param bool $required
     * @return string
     */
    public static function formTextarea($name, $required = false) {
        $code = '
        $'.$name.' = $this->addSimpleTextarea("'.$name.'", __("'.self::humanize($name).'"));';

        if($required) {
            $code .= '
        $'.$name.'->setRequired(true);';
        }

        return $code;
    }

    /**
     * @param $name
     * @param $type
     * @param bool $required
     * @return string
     */
    public static function formSelect($name, $type, $required = false) {
        $opts = str_replace(")", "", str_replace("enum(", "", str_replace("\\", "", $type)));
        $opts = explode(",", $opts);

        $values = "";
        foreach($opts as $opt) {
            $sopt = str_replace("'", "", $opt);
            $values .= '
            "'.$sopt.'" => "'.self::humanize($sopt).'",';
        }

        $code = '
        $'.$name.' = $this->addSimpleSelect("'.$name.'", __("'.self::humanize($name).'"), [
            '.$values.'
        ]);';

        if($required) {
            $code .= '
        $'.$name.'->setRequired(true);';
        }

        return $code;
    }

    /**
     * @param $name
     * @param bool $required
     * @return string
     */
    public static function formDate($name, $required = false) {
        $code = '
        $'.$name.' = $this->addSimpleDatetimepicker("'.$name.'", __("'.self::humanize($name).'"), false, Siberian_Form_Abstract::DATEPICKER);';

        if($required) {
            $code .= '
        $'.$name.'->setRequired(true);';
        }

        return $code;
    }

    /**
     * @param $name
     * @param bool $required
     * @return string
     */
    public static function formDatetime($name, $required = false) {
        $code = '
        $'.$name.' = $this->addSimpleDatetimepicker("'.$name.'", __("'.self::humanize($name).'"), false, Siberian_Form_Abstract::DATETIMEPICKER);';

        if($required) {
            $code .= '
        $'.$name.'->setRequired(true);';
        }

        return $code;
    }

    /**
     * @param $name
     * @param bool $required
     * @return string
     */
    public static function formTime($name, $required = false) {
        $code = '
        $'.$name.' = $this->addSimpleDatetimepicker("'.$name.'", __("'.self::humanize($name).'"), false, Siberian_Form_Abstract::TIMEPICKER);';

        if($required) {
            $code .= '
        $'.$name.'->setRequired(true);';
        }

        return $code;
    }

    /**
     * @param $text
     * @return mixed
     */
    public static function humanize($text) {
        return str_replace('_', ' ', ucwords($text, '_'));
    }

}