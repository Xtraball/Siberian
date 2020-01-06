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
     * @param $valueId
     * @return array|bool
     */
    public function getInappStates($valueId)
    {
        $inAppStates = [
            [
                'state' => 'form2-home',
                'offline' => true,
                'params' => [
                    'value_id' => $valueId,
                ]
            ],
        ];

        return $inAppStates;
    }

    /**
     * @param $optionValue
     * @return array
     */
    public function getAppInitUris ($optionValue): array
    {
        $valueId = $optionValue->getId();

        $featureUrl = __url('/form2/mobile_home/index', ['value_id' => $valueId]);
        $featurePath = __path('/form2/mobile_home/index', ['value_id' => $valueId]);

        return [
            'featureUrl' => $featureUrl,
            'featurePath' => $featurePath,
        ];
    }

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