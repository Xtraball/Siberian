<?php

namespace Form2\Model;

use Core\Model\Base;
use Siberian\Json;

/**
 * Class Field
 * @package Form2\Model
 *
 * @method Db\Table\Field getTable()
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
}
