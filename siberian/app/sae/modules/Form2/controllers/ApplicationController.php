<?php

use Form2\Form\Settings;
use Form2\Model\Result;
use Siberian\Exception;
use Siberian\File;
use Siberian\Json;

/**
 * Class Form2_ApplicationController
 */
class Form2_ApplicationController extends Application_Controller_Default
{
    /**
     * @var array
     */
    public $cache_triggers = [
        'edit-settings' => [
            'tags' => [
                'homepage_app_#APP_ID#',
            ],
        ],
    ];

    /**
     *
     */
    public function editSettingsAction()
    {
        try {
            $optionValue = $this->getCurrentOptionValue();
            $request = $this->getRequest();
            $values = $request->getPost();

            if (!$optionValue->getId()) {
                throw new Exception(p__('form2', "This feature doesn't exists!"));
            }

            if (empty($values)) {
                throw new Exception(p__('form2', 'Values are required!'));
            }

            $form = new Settings();
            if ($form->isValid($values)) {

                $optionValue
                    ->setSettings(Json::encode($values))
                    ->save();

                /** Update touch date, then never expires (until next touch) */
                $optionValue
                    ->touch()
                    ->expires(-1);

                // Clear cache on save!
                $this->cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, [
                    'form2',
                    'value_id_' . $optionValue->getId(),
                ]);

                $payload = [
                    'success' => true,
                    'message' => p__('form2', 'Settings saved'),
                ];
            } else {
                $payload = [
                    'error' => true,
                    'message' => $form->getTextErrors(),
                    'errors' => $form->getTextErrors(true)
                ];
            }
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     *
     */
    public function exportCsvAction()
    {
        try {
            $request = $this->getRequest();
            $valueId = $request->getParam('value_id', null);
            $excludeAnonymous = filter_var($request->getParam('excludeAnonymous', false), FILTER_VALIDATE_BOOLEAN);
            $lastUserRecord = filter_var($request->getParam('lastUserRecord', false), FILTER_VALIDATE_BOOLEAN);

            $results = (new Result())
                ->fetchForCsv($valueId, $excludeAnonymous, $lastUserRecord);

            $headers = [];
            $headers['customer_id'] = p__('form2', 'Customer ID');
            $headers['firstname'] = p__('form2', 'Firstname');
            $headers['lastname'] = p__('form2', 'Lastname');
            $headers['email'] = p__('form2', 'E-mail');

            // Building up ALL Fields (mixed with missing, etc ...)
            foreach ($results as $result) {
                $fields = $result->getPayload();
                foreach ($fields as $field) {
                    $fieldId = $field['field_id'];
                    if (!array_key_exists($fieldId, $headers)) {
                        $headers[$fieldId] = str_replace(';', '-', $field['label']);
                    }
                }
            }

            $headers['date'] = p__('form2', 'Date');

            $rows = [];
            // Filling up ALL Data rows (mixed with missing, etc ...)
            foreach ($results as $result) {
                $fields = $result->getPayload();
                $row = [];
                $row['customer_id'] = $result->getCustomerId();
                $row['firstname'] = $result->getFirstname();
                $row['lastname'] = $result->getLastname();
                $row['email'] = $result->getEmail();

                foreach ($headers as $fieldId => $label) {
                    if (!array_key_exists($fieldId, $row)) {
                        $row[$fieldId] = null;
                    }
                }
                foreach ($fields as $field) {
                    $fieldId = $field['field_id'];
                    if (is_array($field['value'])) {
                        $flatArray = array_flat($field['value']);
                        $v = implode_polyfill(', ', $flatArray);
                    } else {
                        $v = $field['value'];
                    }
                    $newRow = str_replace(';', '-', $v);
                    // Replacing line returns to prevent breakage.
                    $newRow = preg_replace("/[\n\r]/", ' ', $newRow);
                    $row[$fieldId] = $newRow;
                }

                $row['date'] = $result->getCreatedAt();

                $rows[] = $row;
            }

            // E-mail CSV to admins!
            $csvTextLines = [];
            $csvTextLines[] = implode_polyfill(';', array_values($headers));

            foreach ($rows as $row) {
                $csvTextLines[] = implode_polyfill(';', $row);
            }
            $csvText = implode_polyfill("\n", $csvTextLines);

            // uniqid
            $csvPath = path(uniqid('/var/tmp/', true) . '.csv');
            File::putContents($csvPath, $csvText);

            $date = date('Y-m-d_H-i-s');
            $this->_download($csvPath, "form-export-{$date}.csv");

            $payload = [
                'success' => true,
                'downloading' => true,
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }
}
