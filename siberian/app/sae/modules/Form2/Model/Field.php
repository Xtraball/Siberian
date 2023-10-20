<?php

namespace Form2\Model;

use Core\Model\Base;
use Siberian\Json;

/**
 * Class Field
 * @package Form2\Model
 *
 * @method Db\Table\Field getTable()
 * @method integer getId()
 * @method integer getFieldId()
 * @method string getFieldType()
 * @method string getLabel()
 * @method integer getLimit()
 * @method integer getNumberMin()
 * @method integer getNumberMax()
 * @method integer getNumberStep()
 * @method string getDateFormat()
 * @method string getDatetimeFormat()
 * @method string getClickwrap()
 * @method string getClickwrapModaltitle()
 * @method string getImage()
 * @method string getImageAddpicture()
 * @method string getImageAddanotherpicture()
 * @method boolean getIsRequired()
 * @method mixed getDefaultValue()
 * @method $this setFieldType(string $type)
 */
class Field extends Base
{
    /**
     * @var string
     */
    protected $_db_table = Db\Table\Field::class;

    /**
     * @var array
     */
    const WEEK_DAYS = [0, 1, 2, 3, 4, 5, 6];

    /**
     * @param $valueId
     * @return $this
     */
    public function initPosition($valueId): self
    {
        $position = $this->getTable()->getLastPosition($valueId);

        return $this->setData('position', $position['position'] + 1);
    }

    /**
     * @param array $options
     * @return Field
     */
    public function setFieldOptions(array $options): Field
    {
        // Excluding empty options!
        $filteredOptions = [];
        foreach ($options as $index => $option) {
            $label = trim($option['label']);
            $value = trim($option['value']);
            if (!empty($label) && !empty($value)) {
                $filteredOptions[$index] = $option;
            }
        }

        return $this->setData('field_options', base64_encode(Json::encode($filteredOptions)));
    }

    /**
     * @return array|mixed
     */
    public function getFieldOptions()
    {
        try {
            return Json::decode(base64_decode($this->getData('field_options')));
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * @param $days
     * @return Field
     */
    public function setDateDays(array $days): Field
    {
        return $this->setData('date_days', implode_polyfill(',', $days));
    }

    /**
     * @return array
     */
    public function getDateDays(): array
    {
        try {
            return array_values(explode(',', $this->getData('date_days')));
        } catch (\Exception $e) {}

        // Or defaults to all days!
        return self::WEEK_DAYS;
    }

    /**
     * @return array
     */
    public function getDateSkipDays(): array
    {
        return array_values(array_diff(self::WEEK_DAYS, $this->getDateDays()));
    }

    /**
     * @param $days
     * @return Field
     */
    public function setDatetimeDays(array $days): Field
    {
        return $this->setData('datetime_days', implode_polyfill(',', $days));
    }

    /**
     * @return array
     */
    public function getDatetimeDays(): array
    {
        try {
            return array_values(explode(',', $this->getData('datetime_days')));
        } catch (\Exception $e) {}

        // Or defaults to all days!
        return self::WEEK_DAYS;
    }

    /**
     * @return array
     */
    public function getDatetimeSkipDays(): array
    {
        return array_values(array_diff(self::WEEK_DAYS, $this->getDatetimeDays()));
    }

    /**
     * @param $richtext
     * @return Field
     */
    public function setRichtext($richtext): Field
    {
        return $this->setData('richtext', base64_encode($richtext));
    }

    /**
     * @return string
     */
    public function getRichtext(): string
    {
        try {
            return base64_decode($this->getData('richtext'));
        } catch (\Exception $e) {}
        return '';
    }

    /**
     * @param $richtext
     * @return Field
     */
    public function setClickwrapRichtext($richtext): Field
    {
        return $this->setData('clickwrap_richtext', base64_encode($richtext));
    }

    /**
     * @return string
     */
    public function getClickwrapRichtext(): string
    {
        try {
            return base64_decode($this->getData('clickwrap_richtext'));
        } catch (\Exception $e) {}
        return '';
    }

    /**
     * @return array
     */
    public function toEmbedPayload(): array
    {
        $fieldType = $this->getFieldType();
        $field = [
            'field_id' => (integer) $this->getFieldId(),
            'label' => (string) $this->getLabel(),
            'type' => (string) $fieldType,
            'richtext' => (string) $this->getRichtext(),
            'clickwrap' => (string) $this->getClickwrap(),
            'clickwrap_modaltitle' => (string) $this->getClickwrapModaltitle(),
            'clickwrap_richtext' => (string) $this->getClickwrapRichtext(),
            'image' => (string) $this->getImage(),
            'image_addpicture' => (string) $this->getImageAddpicture(),
            'image_addanotherpicture' => (string) $this->getImageAddanotherpicture(),
            'options' => (array) array_values($this->getFieldOptions()),
            'limit' => (integer) $this->getLimit(),
            'min' => (float) $this->getNumberMin(),
            'max' => (float) $this->getNumberMax(),
            'step' => (float) $this->getNumberStep(),
            'date_format' => (string) $this->getDateFormat(),
            'date_skipdays' => $this->getDateSkipDays(),
            'datetime_format' => (string) $this->getDatetimeFormat(),
            'datetime_skipdays' => $this->getDatetimeSkipDays(),
            'is_checked' => false,
            'is_required' => (boolean) $this->getIsRequired(),
        ];
        $defaultValue = (string) $this->getDefaultValue();
        if (!empty($defaultValue)) {
            switch ($fieldType) {
                case 'number':
                    $defaultValue = (float) $defaultValue;
                    break;
                case 'checkbox':
                    $defaultValue = (boolean) $defaultValue;
                    break;
                case 'date':
                case 'datetime':
                    $defaultValue = null;
                    break;
                default:
                    $defaultValue = (string) $defaultValue;
            }
            $field['value'] = $defaultValue;
        }
        if ($fieldType === 'image') {
            $field['value'] = [];
        }

        return $field;
    }
}
