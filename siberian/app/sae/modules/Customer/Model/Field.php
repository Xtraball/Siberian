<?php

namespace Customer\Model;

use Core\Model\Base;

/**
 * Class Field
 * @package Customer\Model
 *
 * @method Db\Table\Field getTable()
 */
class Field extends Base
{
    /**
     * @var array
     */
    public static $requiredFields = [
        "email" => [
            "field_id" => "email-0",
            "type" => "text",
            "label" => "E-mail",
            "value" => "",
            "min" => 0,
            "max" => 0,
            "step" => 0,
            "date_format" => "",
            "datetime_format" => "",
            "linked_to" => "email",
            "show_at" => "both",
            "is_required" => true,
        ],
        "password" => [
            "field_id" => "password-0",
            "type" => "password",
            "label" => "Password",
            "value" => "",
            "min" => 0,
            "max" => 0,
            "step" => 0,
            "date_format" => "",
            "datetime_format" => "",
            "linked_to" => "email",
            "show_at" => "both",
            "is_required" => true,
        ],
        "privacy_policy" => [
            "field_id" => "privacy-policy-0",
            "type" => "checkbox",
            "label" => "I accept the privacy policy",
            "value" => "",
            "min" => 0,
            "max" => 0,
            "step" => 0,
            "date_format" => "",
            "datetime_format" => "",
            "linked_to" => "email",
            "show_at" => "both",
            "is_required" => true,
        ],
    ];

    /**
     * Field constructor.
     * @param array $datas
     * @throws \Zend_Exception
     */
    public function __construct($datas = [])
    {
        parent::__construct($datas);
        $this->_db_table = "Customer\Model\Db\Table\Field";
    }

    /**
     * @param $valueId
     * @return $this
     */
    public function initPosition($valueId)
    {
        $position = $this->getTable()->getLastPosition($valueId);

        return $this->setData("position", $position["position"] + 1);
    }

    /**
     * @param $valueId
     * @param null $customer
     * @return array
     * @throws \Zend_Exception
     */
    public static function buildCustomFields($valueId, $customer = null)
    {
        if (empty($valueId)) {
            return [];
        }

        $fields = (new self())->findAll(
            [
               "value_id = ?" => $valueId,
            ],
            [
                "position ASC"
            ]);

        $stillRequiredFields = self::$requiredFields;
        $customFields = [];
        foreach ($fields as $field) {
            // Clear required fields!
            $linkedTo = $field->getLinkedTo();
            if (array_key_exists($linkedTo, $stillRequiredFields)) {
                unset($stillRequiredFields[$linkedTo]);
            }

            $value = $field->getDefaultValue();
            if ($customer !== null) {
                $value = $customer->getCustomFieldValue($field);
            }

            $customField = [
                "field_id" => (integer) $field->getFieldId(),
                "label" => (string) $field->getLabel(),
                "type" => (string) $field->getFieldType(),
                "min" => (float) $field->getNumberMin(),
                "max" => (float) $field->getNumberMax(),
                "step" => (float) $field->getNumberStep(),
                "date_format" => (string) $field->getDateFormat(),
                "datetime_format" => (string) $field->getDatetimeFormat(),
                "linked_to" => (string) $field->getLinkedTo(),
                "show_at" => (string) $field->getShowAt(),
                "is_required" => (boolean) $field->getIsRequired(),
            ];

            switch ($field->getFieldType()) {
                case "number":
                    $customField["value"] = (float) $value;
                    break;
                case "checkbox":
                    $customField["value"] = (boolean) $value;
                    break;
                default:
                    $customField["value"] = (string) $value;
            }

            $customFields[] = $customField;
        }

        foreach ($stillRequiredFields as $stillRequiredField) {
            $customFields[] = $stillRequiredField;
        }

        return $customFields;
    }
}
