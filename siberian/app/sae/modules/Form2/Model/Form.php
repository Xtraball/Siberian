<?php

namespace Form2\Model;

use Core\Model\Base;
use Siberian\Json;

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
     * @param $optionValue
     * @return array
     */
    public static function getSettings($optionValue): array
    {
        try {
            $settings = Json::decode($optionValue->getSettings());
            $settings['enable_history'] = filter_var($settings['enable_history'], FILTER_VALIDATE_BOOLEAN);
        } catch (\Exception $e) {
            $settings = [
                'email' => [],
                'design' => 'list',
                'enable_history' => true
            ];
        }

        return $settings;
    }


    /**
     * @param null $optionValue
     * @return array|bool
     * @throws \Zend_Exception
     */
    public function getFeaturePayload($optionValue = null)
    {
        $valueId = $optionValue->getId();

        $settings = self::getSettings($optionValue);

        // Customer
        $session = $this->getSession();
        $customer = $session->getCustomer();
        $history = [];
        if ($settings['enable_history'] &&
            $session->isLoggedIn()) {
            try {
                /**
                 * @var $results Result[]
                 */
                $results = (new Result())->findAll(
                    [
                        'value_id = ?' => $valueId,
                        'customer_id = ?' => $customer->getId(),
                        'is_removed = ?' => 0
                    ],
                    [
                        'result_id DESC'
                    ]);

                $history = [];
                foreach ($results as $result) {
                    $history[] = [
                        'result_id' => $result->getId(),
                        'payload' => $result->getPayload(),
                        'created_at' => $result->getCreatedAt(),
                        'timestamp' => $result->getTimestamp()
                    ];
                }
            } catch (\Exception $e) {
                $history = [];
            }
        }

        $payload = [
            'success' => true,
            'pageTitle' => $optionValue->getTabbarName(),
            'cardDesign' => $settings['design'] === 'card',
            'enableHistory' => $settings['enable_history'],
            'history' => $history,
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

    /**
     * @param null $optionValue
     * @return bool
     */
    public function getEmbedPayload($optionValue = null)
    {
        return false;
    }
}

// Class alias for DB purposes!
class_alias(Form::class, 'Form2_Model_Form');