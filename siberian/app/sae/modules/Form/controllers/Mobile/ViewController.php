<?php

use Siberian\File;
use Siberian\Json;

/**
 *
 */
class Form_Mobile_ViewController extends Application_Controller_Mobile_Default
{
    /**
     *
     */
    public function findAction()
    {
        try {
            $request = $this->getRequest();
            $valueId = $request->getParam("value_id", null);

            if (!$valueId) {
                throw new Exception(__("Invalid value_id!"));
            }

            $option = $this->getCurrentOptionValue();
            $form = $option->getObject();
            $sections = $form->getSections();

            $payload = [
                "success" => true,
                "page_title" => $option->getTabbarName(),
                "dateFormat" => $form->getDateFormat(),
                "design" => $form->getDesign(),
                "sections" => [],
            ];

            foreach ($sections as $section) {
                $section_data = [
                    "name" => $section->getName(),
                    "fields" => []
                ];

                $fields = $section->getFields();

                foreach ($fields as $field) {
                    $fieldData = [
                        "id" => (integer)$field->getId(),
                        "type" => (string)$field->getType(),
                        "name" => (string)$field->getName(),
                        "isFilled" => false,
                        "isRequired" => (boolean)$field->isRequired(),
                        "options" => $field->hasOptions() ? $field->getOptions() : []
                    ];

                    if ($field->isRequired()) {
                        $fieldData["name"] .= " *";
                    }

                    $section_data["fields"][] = $fieldData;
                }

                $payload["sections"][] = $section_data;
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
     * Sauvegarde
     */
    public function postAction()
    {

        try {
            if ($data = Json::decode($this->getRequest()->getRawBody())) {

                $optionValue = $this->getCurrentOptionValue();
                $valueId = $optionValue->getId();

                $data = $data["form"];
                $data_image = [];
                $errors = '';
                // Recherche des sections
                $section = new Form_Model_Section();
                $sections = $section->findByValueId($valueId);

                $field = new Form_Model_Field();

                // Date Validator
                $dataChanged = [];
                $dataForDb = [];
                $dates = [];
                $index = 0;

                foreach ($sections as $k => $section) {
                    // Load the fields
                    $section->findFields($section->getId());
                    // Browse the fields
                    foreach ($section->getFields() as $field) {
                        $index++;

                        // If the field has options
                        if ($field->hasOptions()) {

                            // If the data is not empty
                            if (isset($data[$field->getId()])) {
                                // if all checkbox = false
                                $empty_checkbox = false;
                                if (is_array($data[$field->getId()])) {
                                    if (count($data[$field->getId()]) <= count(array_keys($data[$field->getId()], false))) {
                                        $empty_checkbox = true;
                                    }
                                }
                                if (!$empty_checkbox) {
                                    // Browse the field's options
                                    foreach ($field->getOptions() as $option) {

                                        // If it's a multiselect option and there's at least one selected option, store its value
                                        if (is_array($data[$field->getId()])) {
                                            // If the key exists,
                                            if (array_key_exists($option["id"], $data[$field->getId()])) {
                                                if ($data[$field->getId()][$option["id"]]) {
                                                    $dataChanged[$index . ' - ' . $field->getName()][$option["id"]] = $option["name"];
                                                    if (!isset($dataForDb[$index])) {
                                                        $dataForDb[$index] = [
                                                            "field_id" => $field->getId(),
                                                            "label" => $field->getName(),
                                                            "value" => [],
                                                        ];
                                                    }
                                                    $dataForDb[$index]["value"][] = $option["name"];
                                                }
                                            }
                                            // If the current option has been posted, store its value
                                        } else if ($option["id"] == $data[$field->getId()]) {
                                            $dataChanged[$index . ' - ' . $field->getName()] = $option["name"];
                                            $dataForDb[$index] = [
                                                "field_id" => $field->getId(),
                                                "label" => $field->getName(),
                                                "value" => $option["name"]
                                            ];
                                        }
                                    }
                                } else if ($field->isRequired()) {
                                    $errors .= __('<strong>%s</strong> is required<br />', $field->getName());
                                } else {
                                    $dataForDb[$index] = [
                                        "field_id" => $field->getId(),
                                        "label" => $field->getName(),
                                        "value" => null
                                    ];
                                }

                                // If the field is empty and required, add an error
                            } else if ($field->isRequired()) {
                                $errors .= __('<strong>%s</strong> is required<br />', $field->getName());
                            } else {
                                $dataForDb[$index] = [
                                    "field_id" => $field->getId(),
                                    "label" => $field->getName(),
                                    "value" => null
                                ];
                            }
                        } else {
                            // If the field is required
                            if ($field->isRequired()) {
                                // Add an error based on its type (and if it's empty)
                                switch ($field->getType()) {
                                    case "email":
                                        if (empty($data[$field->getId()]) OR !Zend_Validate::is($data[$field->getId()], 'EmailAddress')) {
                                            $errors .= __('<strong>%s</strong> is not valid email address<br />', $field->getName());
                                        }
                                        break;
                                    case "nombre":
                                        if (!isset($data[$field->getId()]) OR !Zend_Validate::is($data[$field->getId()], 'Digits')) {
                                            $errors .= __('<strong>%s</strong> is not a numerical value<br />', $field->getName());
                                        }
                                        break;
                                    case "date":
                                        if (!isset($data[$field->getId()])/* OR !$validator->isValid($data[$field->getId()])*/) {
                                            $errors .= __('<strong>%s</strong> must be a valid date (e.g. dd/mm/yyyy)<br />', $field->getName());
                                        }
                                        break;
                                    default:
                                        if (empty($data[$field->getId()])) {
                                            $errors .= __('<strong>%s</strong> is required<br />', $field->getName());
                                        }
                                        break;
                                }
                            }

                            // If not empty, store its value
                            if (!empty($data[$field->getId()])) {
                                // If the field is an image
                                if ($field->getType() == "image") {
                                    $image = $data[$field->getId()];

                                    if (!preg_match("@^data:image/([^;]+);@", $image, $matches)) {
                                        throw new \Siberian\Exception(__("Unrecognized image format"));
                                    }

                                    $extension = strtolower($matches[1]);

                                    if (!in_array($extension, ["jpg", "jpeg", "png", "gif", "bmp"])) {
                                        throw new \Siberian\Exception(__("Forbidden image format"));
                                    }

                                    $fileName = uniqid() . '.' . $extension;
                                    $relativePath = $this->getCurrentOptionValue()->getImagePathTo();
                                    $fullPath = Application_Model_Application::getBaseImagePath() . $relativePath;
                                    if (!is_dir($fullPath)) {
                                        mkdir($fullPath, 0777, true);
                                    }
                                    $filePath = $fullPath . '/' . $fileName;

                                    $contents = file_get_contents($image);
                                    if ($contents === FALSE) {
                                        throw new \Siberian\Exception(__("No uploaded image"));
                                    }

                                    $res = File::putContents($filePath, $contents);
                                    if ($res === FALSE) {
                                        throw new \Siberian\Exception('Unable to save image');
                                    }

                                    list($width, $height) = getimagesize($fullPath . DS . $fileName);

                                    $max_height = $max_width = 600;
                                    if ($height > $width) {
                                        $image_width = $max_height * $width / $height;
                                        $image_height = $max_height;
                                    } else {
                                        $image_width = $max_width;
                                        $image_height = $max_width * $height / $width;
                                    }
                                    $finalPath = Siberian_Feature::moveUploadedFile($this->getCurrentOptionValue(), $filePath);

                                    $imageUrl = $this->getRequest()->getBaseUrl() . '/images/application' . $finalPath;

                                    $dataChanged[$index . ' - ' . $field->getName()] = '<br/><img width="' . $image_width . '" height="' . $image_height . '" src="' . $imageUrl . '" alt="' . $field->getName() . '" />';
                                    $dataForDb[$index] = [
                                        "field_id" => $field->getId(),
                                        "label" => $field->getName(),
                                        "value" => $imageUrl
                                    ];
                                    // In progress!
                                } else if ($field->getType() == "geoloc") {

                                    if (!is_array($data[$field->getId()])) {
                                        $dataChanged[$index . ' - ' . $field->getName()] = $data[$field->getId()];
                                        $dataForDb[$index] = [
                                            "field_id" => $field->getId(),
                                            "label" => $field->getName(),
                                            "value" => preg_replace("/<br( )?(\/)?>/", " - ", $data[$field->getId()])
                                        ];
                                    } else {
                                        $tmpData = $data[$field->getId()];
                                        $dataChanged[$index . ' - ' . $field->getName()] = sprintf("%s<br />%s, %s",
                                            $tmpData["address"],
                                            $tmpData["coords"]["lat"],
                                            $tmpData["coords"]["lng"]);

                                        $dataForDb[$index] = [
                                            "field_id" => $field->getId(),
                                            "label" => $field->getName(),
                                            "value" => $tmpData
                                        ];
                                    }

                                } else {
                                    $dataChanged[$index . ' - ' . $field->getName()] = $data[$field->getId()];
                                    $dataForDb[$index] = [
                                        "field_id" => $field->getId(),
                                        "label" => $field->getName(),
                                        "value" => preg_replace("/<br( )?(\/)?>/", " - ", $data[$field->getId()])
                                    ];

                                    // Do not alter the date ...
                                    if (array_key_exists($field->getId(), $dates)) {
                                        $dataForDb[$index]["value"] =  $dates[$field->getId()];
                                    }
                                }
                            } else {
                                $dataForDb[$index] = [
                                    "field_id" => $field->getId(),
                                    "label" => $field->getName(),
                                    "value" => null
                                ];
                            }

                        }

                    }
                }

                if (empty($errors)) {

                    $form = $this->getCurrentOptionValue()->getObject();

                    // Save values in db
                    $session = $this->getSession();
                    $customerId = null;
                    if ($session->isLoggedIn()) {
                        $customerId = $session->getCustomerId();
                    }

                    $formResult = new Form_Model_FormResult();
                    $formResult
                        ->setValueId($valueId)
                        ->setCustomerId($customerId)
                        ->setPayload(Json::encode($dataForDb, JSON_UNESCAPED_UNICODE))
                        ->save();

                    // Send e-mail only if filled out!
                    if (!empty($form->getEmail())) {

                        $layout = $this->getLayout()->loadEmail("form", "send_email");
                        $layout->getPartial("content_email")
                            ->setFields($dataChanged);
                        $content = $layout->render();

                        $emails = explode(",", $form->getEmail());
                        $subject = __('Your app\'s form') . " - " . $this->getApplication()->getName() . " - " . $this->getCurrentOptionValue()->getTabbarName();

                        # @version 4.8.7 - SMTP
                        $mail = new Siberian_Mail();
                        $mail->setBodyHtml($content);
                        $mail->setFrom($emails[0], $this->getApplication()->getName());
                        foreach ($emails as $email) {
                            $mail->addTo($email, $subject);
                        }
                        $mail->setSubject($subject);
                        $mail->send();
                    }

                    $payload = [
                        "success" => true,
                        "message" => __("The form has been sent successfully")
                    ];

                } else {

                    $payload = [
                        "error" => true,
                        "message" => $errors
                    ];
                }


            } else {
                throw new \Siberian\Exception(__("No data sent."));
            }

        } catch (Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }

}