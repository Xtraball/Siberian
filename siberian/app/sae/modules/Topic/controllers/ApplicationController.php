<?php

use Siberian\Exception;
use Siberian\Feature;
use Siberian\File;

class Topic_ApplicationController extends Application_Controller_Default
{

    /**
     * @var array
     */
    public $cache_triggers = [
        "save" => [
            "tags" => [
                "homepage_app_#APP_ID#"
            ],
        ],
        "editcategory" => [
            "tags" => [
                "homepage_app_#APP_ID#"
            ],
        ],
        "editpostcategory" => [
            "tags" => [
                "homepage_app_#APP_ID#"
            ],
        ],
        "editdescription" => [
            "tags" => [
                "homepage_app_#APP_ID#"
            ],
        ],
        "delete" => [
            "tags" => [
                "homepage_app_#APP_ID#"
            ],
        ],
        "order" => [
            "tags" => [
                "homepage_app_#APP_ID#"
            ],
        ],
    ];

    public function saveAction() {
        try {
            if($data = $this->getRequest()->getPost()) {

                if(empty($data['name'])) throw new Exception(__('Please, fill out all fields'));

                $topic = $this->getCurrentOptionValue()->getObject();
                if(!$topic->getId()) {
                    throw new Exception(__('An error occurred while saving. Please try again later.'));
                }

                $topic->setName($datas['name'])
                    ->setDescription($datas['description'])
                    ->save()
                ;

                /** Update touch date, then never expires (until next touch) */
                $this->getCurrentOptionValue()
                    ->touch()
                    ->expires(-1);

                $html = [
                    'success' => '1',
                    'create_store' => $mcommerce->getStores()->count() == 0,
                    'success_message' => __('Info successfully saved'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                ];

            }
            else {
                throw new Exception(__('An error occurred while saving. Please try again later.'));
            }

        }
        catch(Exception $e) {
            $html = [
                'error' => 1,
                'message' => $e->getMessage(),
                'message_button' => 1,
                'message_loader' => 1
            ];
        }

        $this->_sendJson($html);
    }

    public function editcategoryAction() {

        $category = new Topic_Model_Category();
        if($data = $this->getRequest()->getPost()) {
            if(!empty($data["category_id"])) {
                $category->find($data["category_id"]);
            }
        }

        $html = $this->getLayout()->addPartial('category_form', 'admin_view_default', 'topic/category/edit.phtml')
            ->setOptionValue($this->getCurrentOptionValue())
            ->setParentId($this->getRequest()->getPost('parent_id'))
            ->setCurrentCategory($category)
            ->toHtml()
        ;

        $html = [
            'form_html' => $html,
            'category_id' => $category->getId()
        ];

        $this->_sendJson($html);
    }

    public function editpostcategoryAction() {
        if($data = $this->getRequest()->getPost()) {

            try {
                $isNew = false;
                if(!$data["category_id"]) {
                    $isNew = true;
                    $topic = new Topic_Model_Topic();
                    $topic->find(["value_id" => $this->getCurrentOptionValue()->getValueId()]);

                    if (!$topic->getId()) {
                        throw new Exception(__('An error occurred while saving. Please try again later.'));
                    }

                    $category = new Topic_Model_Category();
                    $position = $category->getMaxPosition($topic->getId());

                    $data["position"] = $position?$position+1:1;
                    $data["topic_id"] = $topic->getId();
                } else {
                    $category = new Topic_Model_Category($data["category_id"]);
                }

                if(!empty($data["file"])) {
                    $picture = $data["file"];
                    $relative_path = '/features/topic/';
                    $folder = Application_Model_Application::getBaseImagePath() . $relative_path;
                    $path = Application_Model_Application::getBaseImagePath() . $relative_path;
                    $file = Core_Model_Directory::getTmpDirectory(true) . '/' . $picture;

                    if (file_exists($file)) {
                        if (!is_dir($path)) mkdir($path, 0777, true);
                        if (!copy($file, $folder . $picture)) {
                            throw new exception(__('An error occurred while saving. Please try again later.'));
                        } else {
                            $data['picture'] = $relative_path . $picture;
                        }
                    }
                }

                if($data["remove_picture"]) {
                    $data['picture'] = null;
                }

                $category->addData($data)
                    ->save()
                ;

                /** Update touch date, then never expires (until next touch) */
                $this->getCurrentOptionValue()
                    ->touch()
                    ->expires(-1);

                $html = [
                    'is_new' => (int) $isNew,
                    'category_id' => $category->getId(),
                    'category_label' => $category->getName(),
                    'success' => '1',
                    'success_message' => __('Category successfully saved.'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                ];

                if($isNew) {
                    $html['row_html'] = $this->getLayout()->addPartial('child_'.$category->getId(), 'admin_view_default', 'topic/application/edit/list.phtml')
                        ->setCategory($category)
                        ->toHtml()
                    ;
                }

            }
            catch(Exception $e) {
                $html = [
                    'error' => 1,
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                ];
            }

            $this->_sendJson($html);

        }
    }

    public function editdescriptionAction() {
        if($data = $this->getRequest()->getPost()) {
            try {
                $topic = new Topic_Model_Topic();
                $topic->find(["value_id" => $this->getCurrentOptionValue()->getValueId()]);

                if (!$topic->getId()) {
                    throw new Exception(__('An error occurred while saving. Please try again later.'));
                }

                $topic->setData($data)->save();

                /** Update touch date, then never expires (until next touch) */
                $this->getCurrentOptionValue()
                    ->touch()
                    ->expires(-1);

                $payload = [
                    "success" => true,
                    "message" => __("Description saved."),
                ];

            } catch(Exception $e) {
                $payload = [
                    "error" => true,
                    "message" => $e->getMessage(),
                ];
            }

            $this->_sendJson($payload);
        }
    }

    public function orderAction() {
        if($datas = $this->getRequest()->getParams()) {

            try {

                // Récupère les positions
                $positions = $this->getRequest()->getParam('category');
                if(empty($positions)) throw new Exception(__('An error occurred while saving. Please try again later.'));

                $position = 0;
                foreach($positions as $index => $parent_category) {
                    if($parent_category == "null") {
                        $parent_category = null;
                    }
                    $category = new Topic_Model_Category();
                    $category->find($index, 'category_id');
                    $category
                        ->setParentId($parent_category)
                        ->setPosition($position)
                        ->save();
                    $position++;
                }

                /** Update touch date, then never expires (until next touch) */
                $this->getCurrentOptionValue()
                    ->touch()
                    ->expires(-1);

                $payload = [
                    "success" => true,
                    "message" => __("Order saved."),
                ];

            } catch(Exception $e) {
                $payload = [
                    "error" => true,
                    "message" => $e->getMessage(),
                ];
            }

            $this->_sendJson($payload);

        }
    }

    public function deleteAction() {

        if($data = $this->getRequest()->getParams()) {

            try {
                if(empty($data['category_id'])) {
                    throw new Exception(__('An error occurred while saving. Please try again later.'));
                }

                $category = new Topic_Model_Category();
                $category->find($data['category_id']);

                if(!$category->getId()) {
                    throw new Exception(__('An error occurred while saving. Please try again later.'));
                }

                $category->delete();

                /** Update touch date, then never expires (until next touch) */
                $this->getCurrentOptionValue()
                    ->touch()
                    ->expires(-1);

                $html = [
                    'success' => 1
                ];
            }
            catch(Exception $e) {
                $html = [
                    'error' => 1,
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                ];
            }

            $this->_sendJson($html);

        }

    }

    public function cropAction() {

        if($datas = $this->getRequest()->getPost()) {
            try {
                $html = [];
                $uploader = new Core_Model_Lib_Uploader();
                $file = $uploader->savecrop($datas);
                $html = [
                    'success' => 1,
                    'file' => $file,
                    'message_success' => 'Enregistrement réussi',
                    'message_button' => 0,
                    'message_timeout' => 2,
                ];
            } catch (Exception $e) {
                $html = [
                    'error' => 1,
                    'message' => $e->getMessage()
                ];
            }

            $this->_sendJson($html);

        }

    }

    public function importUserAction ()
    {
        try {
            if (empty($_FILES) || empty($_FILES["files"]["name"])) {
                throw new Exception("#908-01: " . p__("topic", "No file sent."));
            }

            $tmp = tmp(true);
            $tmpPath = $tmp . "/" . $_FILES["files"]["name"][0];
            if (!rename($_FILES['files']['tmp_name'][0], $tmpPath)) {
                throw new Exception("#908-02: " . p__("topic", "Unable to write file."));
            }

            // Detect if it's a simple feature or a complete template Application!
            $filetype = pathinfo($tmpPath, PATHINFO_EXTENSION);
            switch ($filetype) {
                case "csv":
                    $this->importCsv($tmpPath);
                    break;
                case "json":
                    $this->importJson($tmpPath);
                    break;
                case "yml":
                    $this->importYaml($tmpPath);
                    break;
            }

            $payload = [
                "success" => true,
                "message" => p__("topic", "Import success."),
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
     * @param $path
     * @throws Exception
     */
    public function importCsv($path)
    {
        $optionValue = $this->getCurrentOptionValue();
        $topic = (new Topic_Model_Topic())->find($optionValue->getId(), "value_id");

        if (!$topic->getId()) {
            throw new Exception("#908-03: " . p__("topic", "This topic doesn't exists."));
        }

        $csvResource = fopen($path, "r");
        $headers = fgetcsv($csvResource, 1024, ";", '"');

        $hasTitle = false;
        $hasDescription = false;
        $hasPicture = false;
        $titleIndex = 0;
        $descriptionIndex = 0;
        $pictureIndex = 0;
        foreach ($headers as $index => $header) {
            if ($header === "title") {
                $titleIndex = $index;
                $hasTitle = true;
            }

            if ($header === "description") {
                $descriptionIndex = $index;
                $hasDescription = true;
            }

            if ($header === "picture") {
                $pictureIndex = $index;
                $hasPicture = true;
            }
        }

        if (!$hasTitle && !$hasDescription) {
            throw new Exception(p__("`title` and `description` are missing."));
        }
        if (!$hasTitle) {
            throw new Exception(p__("`title` is missing."));
        }
        if (!$hasDescription) {
            throw new Exception(p__("`description` is missing."));
        }

        $position = 0;
        while ($line = fgetcsv($csvResource, 1024, ";", '"')) {
            $topicCategory = new Topic_Model_Category();
            $topicCategory
                ->setTopicId($topic->getId())
                ->setName($line[$titleIndex])
                ->setDescription($line[$descriptionIndex])
                ->setPosition($position++);

            if ($hasPicture) {
                try {
                    $tmpPicture = file_get_contents($line[$pictureIndex]);
                    if (!$tmpPicture) {
                        throw new Exception("silent_fail");
                    }

                    // Searching for a mime/

                    $tmpPath = tmp(true) . "/" . uniqid(true);
                    File::putContents($tmpPath, $tmpPicture);

                    if (!extension_loaded("exif")) {
                        throw new Exception("exif_missing");
                    }

                    $type = exif_imagetype($tmpPath);
                    if (!in_array($type, [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_BMP])) {
                        throw new Exception("invalid_type");
                    }

                    unlink($tmpPath);
                    switch ($type) {
                        case IMAGETYPE_GIF:
                            $tmpPath = $tmpPath . ".gif";
                            break;
                        case IMAGETYPE_JPEG:
                            $tmpPath = $tmpPath . ".jpg";
                            break;
                        case IMAGETYPE_PNG:
                            $tmpPath = $tmpPath . ".png";
                            break;
                        case IMAGETYPE_BMP:
                            $tmpPath = $tmpPath . ".bmp";
                            break;
                    }

                    File::putContents($tmpPath, $tmpPicture);
                    $path = Feature::saveImageForOption($optionValue, $tmpPath);

                    $topicCategory->setPicture($path);
                } catch (\Exception $e) {
                    // Something went wrong while fetching picture!
                }
            }

            $topicCategory
                ->save();
        }
        fclose($csvResource);
    }

    public function importJson($path)
    {

    }

    public function importYaml($path)
    {

    }

    /**
     * @param $option
     * @return string
     * @throws Exception
     */
    public function exportAction() {
        if($this->getCurrentOptionValue()) {
            $topic = new Topic_Model_Topic();
            $result = $topic->exportAction($this->getCurrentOptionValue());

            $this->_download($result, "topic-".date("Y-m-d_h-i-s").".yml", "text/x-yaml");
        }
    }

}