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
 * @method integer getNumberMin()
 * @method integer getNumberMax()
 * @method integer getNumberStep()
 * @method string getDateFormat()
 * @method string getDatetimeFormat()
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
            'clickwrap_richtext' => (string) $this->getClickwrapRichtext(),
            'image' => (string) $this->getImage(),
            'options' => (array) array_values($this->getFieldOptions()),
            'limit' => (float) $this->getLimit(),
            'min' => (float) $this->getNumberMin(),
            'max' => (float) $this->getNumberMax(),
            'step' => (float) $this->getNumberStep(),
            'date_format' => (string) $this->getDateFormat(),
            'datetime_format' => (string) $this->getDatetimeFormat(),
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
