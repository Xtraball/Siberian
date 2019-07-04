<?php

use Siberian\Exception;
use Customer\Form\Field as FormField;
use Customer\Form\Field\Delete as FormDeleteField;
use Customer\Model\Field;

/**
 * Class Customer_FieldController
 */
class Customer_FieldController extends Application_Controller_Default
{
    /**
     * @var array
     */
    public $cache_triggers = [
        "update-positions" => [
            "tags" => [
                "homepage_app_#APP_ID#",
            ],
        ],
        "edit" => [
            "tags" => [
                "homepage_app_#APP_ID#",
            ],
        ],
    ];

    public function loadFormAction()
    {
        try {
            $request = $this->getRequest();
            $fieldId = $request->getParam("field_id", null);

            $field = (new Field())
                ->find($fieldId);

            if (!$field->getId()) {
                throw new Exception(p__("customer", "The field you are trying to edit doesn't exists."));
            }

            $form = new FormField();
            $form->populate($field->getData());
            $form->removeNav("nav-fields");
            $submit = $form->addSubmit(p__("customer", "Save"));
            $submit->addClass("pull-right");

            $formId = "form-field-edit-{$fieldId}";

            $form->binderField($formId);
            $form->setAttrib("id", $formId);

            $payload = [
                "success" => true,
                "form" => $form->render(),
                "message" => p__("customer", "Success"),
            ];
        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage(),
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
                $fieldId = $params["field_id"];
                $field = (new Field())
                    ->find($fieldId);

                $field->delete();
                
                // Update touch date, then never expires (until next touch)!
                $optionValue
                    ->touch()
                    ->expires(-1);
            }

            $payload = [
                "success" => true,
                "message" => p__("customer", "Success"),
            ];
        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage(),
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
            $indexes = $request->getParam("indexes", null);

            if (empty($indexes)) {
                throw new Exception(p__("customer", "Nothing to do!"));
            }

            foreach ($indexes as $index => $fieldId) {
                $field = (new Field())
                    ->find($fieldId);

                if (!$field->getId()) {
                    throw new Exception(p__("customer", "Something went wrong, the field do not exists!"));
                }

                $field
                    ->setPosition($index + 1)
                    ->save();
            }

            $payload = [
                "success" => true,
                "message" => p__("customer", "Success"),
            ];
        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage(),
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
                $field = (new Field())->find($params["field_id"]);

                $field
                    ->setData($params)
                ->setFieldType($form->getValue("field_type"));

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
                    "success" => true,
                    "message" => p__("customer", "Success"),
                ];
            } else {
                $payload = [
                    "error" => true,
                    "message" => $form->getTextErrors(),
                    "errors" => $form->getTextErrors(true)
                ];
            }
        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }
}
