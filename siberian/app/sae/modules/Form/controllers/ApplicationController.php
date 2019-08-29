<?php

use Siberian\File;
use Siberian\Json;

/**
 * Class Form_ApplicationController
 */
class Form_ApplicationController extends Application_Controller_Default
{
    /**
     *
     */
    public function editSettingsAction()
    {
        try {
            $optionValue = $this->getCurrentOptionValue();
            $valueId = $optionValue->getId();
            $request = $this->getRequest();
            $values = $request->getPost();

            if (!$optionValue->getId()) {
                throw new Exception(p__("form","This feature doesn't exists!"));
            }

            if (empty($values)) {
                throw new Exception(p__("form","Values are required!"));
            }

            $currentForm = (new Form_Model_Form())->find($valueId, "value_id");
            if (!$currentForm->getId()) {
                $currentForm->setValueId($valueId)->save();
            }

            $form = new Form_Form_Settings();
            if ($form->isValid($values)) {

                $currentForm
                    ->setEmail($values["email"])
                    ->setDesign($values["design"])
                    ->setDateFormat($values["date_format"])
                    ->save();

                /** Update touch date, then never expires (until next touch) */
                $optionValue
                    ->touch()
                    ->expires(-1);

                // Clear cache on save!
                $this->cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, [
                    "form",
                    "value_id_" . $optionValue->getId(),
                ]);

                $payload = [
                    "success" => true,
                    "message" => p__("form","Settings saved"),
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

    /**
     *
     */
    public function exportCsvAction()
    {
        try {
            $request = $this->getRequest();
            $valueId = $request->getParam("value_id", null);
            $excludeAnonymous = filter_var($request->getParam("excludeAnonymous", false), FILTER_VALIDATE_BOOLEAN);
            $lastUserRecord = filter_var($request->getParam("lastUserRecord", false), FILTER_VALIDATE_BOOLEAN);

            $results = (new Form_Model_FormResult())
                ->fetchForCsv($valueId, $excludeAnonymous, $lastUserRecord);

            $headers = [];
            $headers["customer_id"] = p__("form", "Customer ID");
            $headers["firstname"] = p__("form", "Firstname");
            $headers["lastname"] = p__("form", "Lastname");
            $headers["email"] = p__("form", "E-mail");

            // Building up ALL Fields (mixed with missing, etc ...)
            foreach ($results as $result) {
                $fields = Json::decode($result->getPayload());
                foreach ($fields as $field) {
                    $fieldId = $field["field_id"];
                    if (!array_key_exists($fieldId, $headers)) {
                        $headers[$fieldId] = str_replace(";", "-", $field["label"]);
                    }
                }
            }

            $headers["date"] = p__("form", "Date");

            $rows = [];
            // Filling up ALL Data rows (mixed with missing, etc ...)
            foreach ($results as $result) {
                $fields = Json::decode($result->getPayload());
                $row = [];
                $row["customer_id"] = $result->getCustomerId();
                $row["firstname"] = $result->getFirstname();
                $row["lastname"] = $result->getLastname();
                $row["email"] = $result->getEmail();

                foreach ($headers as $fieldId => $label) {
                    if (!array_key_exists($fieldId, $row)) {
                        $row[$fieldId] = null;
                    }
                }
                foreach ($fields as $field) {
                    $fieldId = $field["field_id"];
                    if (is_array($field["value"])) {
                        $flatArray = array_flat($field["value"]);
                        $v = join(", ", $flatArray);
                    } else {
                        $v = $field["value"];
                    }
                    $row[$fieldId] = str_replace(";", "-", $v);
                }

                $row["date"] = $result->getCreatedAt();

                $rows[] = $row;
            }

            // E-mail CSV to admins!
            $csvTextLines = [];
            $csvTextLines[] = join(";", array_values($headers));

            foreach ($rows as $row) {
                $csvTextLines[] = join(";", $row);
            }
            $csvText = join("\n", $csvTextLines);

            // uniqid
            $csvPath = path("/var/tmp/" . uniqid() . ".csv");
            File::putContents($csvPath, $csvText);

            $date = date("Y-m-d_H-i-s");
            $this->_download($csvPath, "form-export-{$date}.csv");

            $payload = [
                "success" => true,
                "downloading" => true,
            ];
        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage(),
            ];
        }
        
        $this->_sendJson($payload);
    }
}