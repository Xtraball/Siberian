<?php

use Siberian\Exception;
use Form2\Form\Field as FormField;
use Form2\Form\Field\Delete as FormDeleteField;
use Form2\Model\Field;
use Siberian\Feature;

/**
 * Class Form2_FieldController
 */
class Form2_FieldController extends Application_Controller_Default
{
    /**
     * @var array
     */
    public $cache_triggers = [
        'update-positions' => [
            'tags' => [
                'homepage_app_#APP_ID#',
            ],
        ],
        'delete' => [
            'tags' => [
                'homepage_app_#APP_ID#',
            ],
        ],
        'edit' => [
            'tags' => [
                'homepage_app_#APP_ID#',
            ],
        ],
    ];

    /**
     *
     */
    public function loadFormAction()
    {
        try {
            $request = $this->getRequest();
            $fieldId = $request->getParam('field_id', null);

            $field = (new Field())
                ->find($fieldId);

            if (!$field || !$field->getId()) {
                throw new Exception(p__('form2', "The field you are trying to edit doesn't exists."));
            }

            $form = new FormField();

            $selectOptions = $field->getFieldOptions();

            $fieldData = $field->getData();
            $fieldData['richtext'] = $field->getRichtext();
            $fieldData['clickwrap_richtext'] = $field->getClickwrapRichtext();
            if (empty($fieldData['limit'])) {
                $fieldData['limit'] = 1;
            }
            $fieldData['date_days'] = $field->getDateDays();
            $fieldData['datetime_days'] = $field->getDatetimeDays();

            $form->populate($fieldData);
            $form->removeNav('nav-fields');
            $submit = $form->addSubmit(p__('form2', 'Save'));
            $submit->addClass('pull-right');

            // richtext uuid
            $form->getElement('richtext')->setAttrib('id', 'richtext-edit-' . $field->getId());

            $formId = "form-field-edit-{$fieldId}";

            $form->binderField($formId, $selectOptions);
            $form->setAttrib('id', $formId);

            $payload = [
                'success' => true,
                'form' => $form->render(),
                'message' => p__('form2', 'Success'),
            ];
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
    public function deleteAction ()
    {
        try {
            $request = $this->getRequest();
            $params = $request->getPost();
            $optionValue = $this->getCurrentOptionValue();

            $form = new FormDeleteField();
            if ($form->isValid($params)) {
                $fieldId = $params['field_id'];
                $field = (new Field())
                    ->find($fieldId);

                if (!$field || !$field->getId()) {
                    throw new Exception(p__('form2', 'Something went wrong, the field do not exists!'));
                }

                $field->delete();
                
                // Update touch date, then never expires (until next touch)!
                $optionValue
                    ->touch()
                    ->expires(-1);
            }

            $payload = [
                'success' => true,
                'message' => p__('form2', 'Success'),
            ];
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
    public function updatePositionsAction()
    {
        try {
            $request = $this->getRequest();
            $indexes = $request->getParam('indexes', null);

            if (empty($indexes)) {
                throw new Exception(p__('form2', 'Nothing to do!'));
            }

            foreach ($indexes as $index => $fieldId) {
                $field = (new Field())
                    ->find($fieldId);

                if (!$field || !$field->getId()) {
                    throw new Exception(p__('form2', 'Something went wrong, the field do not exists!'));
                }

                $field
                    ->setPosition($index + 1)
                    ->save();
            }

            $payload = [
                'success' => true,
                'message' => p__('form2', 'Success'),
            ];
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
    public function editAction()
    {
        $request = $this->getRequest();
        $params = $request->getPost();

        $form = new FormField();
        try {
            if ($form->isValid($params)) {
                // Do whatever you need when form is valid!
                $optionValue = $this->getCurrentOptionValue();

                /**
                 * @var $field Field
                 */
                $field = (new Field())->find($params['field_id']);

                $field
                    ->setData($params)
                    ->setRichtext($params['richtext'])
                    ->setClickwrapRichtext($params['clickwrap_richtext'])
                    ->setDateDays($params['date_days'])
                    ->setDatetimeDays($params['datetime_days'])
                    ->setFieldType($params['field_type']);

                if (array_key_exists('select_options', $params) &&
                    is_array($params['select_options'])) {
                    $field->setFieldOptions($params['select_options']);
                }

                // Only if image type!
                if ($params['field_type'] === 'illustration') {
                    Feature::formImageForOption($optionValue, $field, $params, 'image', true);
                }

                if (!$field->getId()) {
                    // Set the position + 1
                    $field->initPosition($optionValue->getId());
                }

                $field->save();

                $this
                    ->getCurrentOptionValue()
                    ->touch()
                    ->expires(-1);

                $payload = [
                    'success' => true,
                    'message' => p__('form2', 'Success'),
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
}
