<?php

namespace Form2\Model;

use Core\Model\Base;

/**
 * Class Form
 * @package Form2\Model
 */
class Form extends Base
{
    /**
     * @param null $optionValue
     * @return array|bool
     * @throws \Zend_Exception
     */
    public function getEmbedPayload($optionValue = null)
    {
        $valueId = $optionValue->getId();
        $payload = [
            'success' => true,
            'page_title' => $optionValue->getTabbarName()
        ];

        /**
         * @var $fields Field[]
         */
        $fields = (new Field())->findAll([
            'value_id = ?' => $valueId
        ], [
            'position ASC'
        ]);

        $formFields = [];
        foreach ($fields as $field) {
            $formFields[] = $field->toEmbedPayload();
        }

        $payload['formFields'] = $formFields;

        return $payload;
    }
}

// Class alias for DB purposes!
class_alias('Form2\Model\Form', 'Form2_Model_Form');