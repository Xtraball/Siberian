<?php

namespace Customer\Form;

use Siberian_Form_Abstract as FormAbstract;

/**
 * Class Field
 * @package Customer\Form
 */
class Field extends FormAbstract
{
    /**
     * @var array
     */
    public static $columns = [
        "custom" => "Custom (non-standard field)",
        "civility" => "Civility",
        "firstname" => "First name",
        "lastname" => "Last name",
        "nickname" => "Nickname",
        "birthdate" => "Birthdate",
        "phone" => "Phone",
        "mobile" => "Mobile phone",
        "email" => "E-mail",
        "password" => "Password",
        "privacy_policy" => "Privacy policy",
    ];

    /**
     * @var array
     */
    public static $types = [
        "divider" => "Divider (title)",
        "spacer" => "Spacer (white space)",
        "number" => "Number",
        //"select" => "Select",
        "checkbox" => "Checkbox",
        "password" => "Password",
        "text" => "Text",
        "textarea" => "Textarea",
        "date" => "Date",
        "datetime" => "Date & time",
    ];

    public static $dateFormats = [
        "MM/DD/YYYY" => "MM/DD/YYYY",
        "DD/MM/YYYY" => "DD/MM/YYYY",
        "MM DD YYYY" => "MM DD YYYY",
        "DD MM YYYY" => "DD MM YYYY",
        "YYYY-MM-DD" => "YYYY-MM-DD",
        "YYYY MM DD" => "YYYY MM DD",
    ];

    public static $datetimeFormats = [
        "MM/DD/YYYY HH:mm" => "MM/DD/YYYY HH:mm",
        "DD/MM/YYYY HH:mm" => "DD/MM/YYYY HH:mm",
        "MM DD YYYY HH:mm" => "MM DD YYYY HH:mm",
        "DD MM YYYY HH:mm" => "DD MM YYYY HH:mm",
        "YYYY-MM-DD HH:mm" => "YYYY-MM-DD HH:mm",
        "YYYY MM DD HH:mm" => "YYYY MM DD HH:mm",
    ];

    public static $showAt = [
        "registration" => "Registration only",
        "profile" => "Profile only",
        "both" => "Registration & profile",
    ];

    /**
     * @throws \Zend_Form_Exception
     * @throws \Zend_Validate_Exception
     */
    public function init()
    {
        parent::init();

        $this
            ->setAction(__path("/customer/field/edit"))
            ->setAttrib("id", "form-edit-field");

        self::addClass("create", $this);

        $this->addNav("nav-fields", __("Save"));

        $this->addSimpleHidden("field_id");
        $this->addSimpleHidden("value_id");
        $this->addSimpleHidden("position");

        $label = $this->addSimpleText("label", p__("customer", "Label"));
        $label->setRequired(true);

        $fields = [];
        foreach (self::$columns as $key => $label) {
            $fields[$key] = p__("customer", $label);
        }

        $linkedTo = $this->addSimpleSelect("linked_to", p__("customer", "Linked to column"), $fields);
        $linkedTo->setRequired(true);

        $showAt = $this->addSimpleSelect("show_at", p__("customer", "Show at?"), self::$showAt);
        $showAt->setRequired(true);

        $fieldTypes = [];
        foreach (self::$types as $key => $label) {
            $fieldTypes[$key] = p__("customer", $label);
        }

        $type = $this->addSimpleSelect("field_type", p__("customer", "Type"), $fieldTypes);
        $type->setRequired(true);

        // Number
        $this->addSimpleNumber("number_min", p__("customer", "Min. value"));
        $this->addSimpleNumber("number_max", p__("customer", "Max. value"));
        $this->addSimpleNumber("number_step", p__("customer", "Step"));

        $this->groupElements("group_number", ["number_min", "number_max", "number_step"], p__("customer", "Number options"));

        // Date
        $this->addSimpleSelect("date_format", p__("customer", "Date format"), self::$dateFormats);

        $this->groupElements("group_date", ["date_format"], p__("customer", "Date options"));

        // Datetime
        $this->addSimpleSelect("datetime_format", p__("customer", "Date & time format"), self::$datetimeFormats);

        $this->groupElements("group_datetime", ["datetime_format"], p__("customer", "Date & time options"));

        // Default
        $this->addSimpleText("default_value", p__("customer", "Default value"));

        // Required
        $this->addSimpleCheckbox("is_required", p__("customer", "Required?"));
    }

    /**
     * @param $formId
     */
    public function binderField($formId)
    {
        $type = $this->getElement("field_type")->getValue();
        $linkedTo = $this->getElement("linked_to")->getValue();

        $js = <<<JS
<script type="text/javascript">
$(document).ready(function () {
    window.binderFormField("{$formId}");
    window.toggleGroups("{$formId}", "{$type}");
    window.toggleLinkedTo("{$formId}", "{$linkedTo}");
});
</script>
JS;

        $this->addMarkup($js);
    }

    /**
     * @param array $data
     * @return bool
     * @throws \Zend_Form_Exception
     */
    public function isValid($data)
    {
        if ($data["linked_to"] === "custom") {
            switch ($data["field_type"]) {
                case "number":
                    $this->getElement("number_min")->setRequired(true);
                    $this->getElement("number_max")->setRequired(true);
                    $this->getElement("number_step")->setRequired(true);
                    break;
                case "date":
                    $this->getElement("date_format")->setRequired(true);
                    break;
                case "datetime":
                    $this->getElement("datetime_format")->setRequired(true);
                    break;
            }
        }

        return parent::isValid($data);
    }
}