<?php

class Preview_Backoffice_EditController extends Backoffice_Controller_Default
{

    public function loadAction() {

        $html = array(
            "title" => $this->_("Previews"),
            "icon" => "fa-desktop",
        );

        $this->_sendHtml($html);

    }

    public function findAction() {

        $previews = new Preview_Model_Preview();

        $data = array();
        if($this->getRequest()->getParam("preview_id")) {
            $previews = $previews->findAll(array(
                "aop.preview_id" => $this->getRequest()->getParam("preview_id")
            ));

            $data_tmp = array();
            foreach($previews as $preview) {
                if($preview->getlibraryId()) {
                    $images = $preview->findImages();
                    $images_data = array();
                    foreach ($images as $image) {
                        $images_data[] = array(
                            "id" => $image->getImageId(),
                            "link" => $image->getlink(),
                            "new" => 0,
                            "to_delete" => 0
                        );
                    }
                }

                if($preview->getPreviewId()) {
                    $data_tmp[$preview->getLanguageCode()] = array(
                        "title" => $preview->getTitle(),
                        "description" => $preview->getDescription(),
                        "language_code" => $preview->getLanguageCode(),
                        "from_database" => 1,
                        "images" => $images_data
                    );
                }
            }

            if(!empty($data_tmp)) {
                $data["previews"] = $data_tmp;
            }

            $data["section_title_one"] = $this->_("Edit the preview");
        } else {
            $data["section_title_one"] = $this->_("Create a new preview");
            $option = new Application_Model_Option();
            $option_list = array();
            $options = $option->findAll(array(), 'position ASC');
            foreach($options as $option) {
                $option_list[$option->getId()] = $option->getName();
            }
            $data["options"] = $option_list;
        }

        $data["section_title_two"] = $this->_("Preview images");

        $languages = Core_Model_Language::getLanguages();
        $language_list = array();
        foreach($languages as $language) {
            $language_list[$language->getCode()] = $language->getName();
        }
        $data["languages"] = $language_list;
        $data["current_language"] = Core_Model_Language::getCurrentLanguage();
        $this->_sendHtml($data);

    }

    public function saveAction() {

        if($data = Zend_Json::decode($this->getRequest()->getRawBody())) {

            try {
                $previews = $data["previews"];

                $option_id = !empty($data["option_id"]) ? $data["option_id"] : null;
                $preview_id = !empty($data["preview_id"]) ? $data["preview_id"] : null;

                if(empty($previews)) throw new Exception($this->_("An error occurred while saving your previews. Please try again later."));

                $preview = new Preview_Model_Preview();

                if(!empty($option_id) AND empty($preview_id)){
                    //No preview for this option yet, we create one if it doesn't exists.
                    $preview->find($option_id,"option_id");
                    if($preview->getId()){
                        throw new Exception($this->_("Sorry, but an existing preview for this feature has been found. Please edit existing one."));
                    }

                    $preview->setData("option_id",$option_id);
                } else {
                    //Existing preview
                    if(!empty($preview_id)) {
                        $preview->find($data["preview_id"]);
                    } else {
                        throw new Exception($this->_("An error occurred while saving your previews. Please try again later."));
                    }
                }

                $previews_language_data = array();
                foreach($previews as $language_code => $data) {
                    $option = new Application_Model_Option();
                    $option->find($preview->getOptionId());

                    if($preview->getId()) {
                        $library_id = $preview->findLibraryIdByLanguageCode($language_code);
                    } else {
                        $library_id = null;
                    }

                    if(!$library_id){
                        $library = new Media_Model_Library();
                        $library->setName('preview_'.$language_code.'_'.$option->getCode())->save();
                        $data["library_id"] = $library->getId();
                    } else {
                        $data["library_id"] = $library_id;
                    }

                    //IMAGES------------------------------------------------
                    foreach($data["images"] as $key => $image) {
                        $library_image = new Media_Model_Library_Image();

                        //We only copy new files
                        if($image["new"] == 1) {
                            $old_path = Core_Model_Directory::getTmpDirectory(true);
                            $new_path = Core_Model_Directory::getBasePathTo("images/previews/" . $language_code . "/" . $option->getCode());
                            $new_path_base = Core_Model_Directory::getPathTo("images/previews/" . $language_code . "/" . $option->getCode());

                            if (!is_dir($new_path)) {
                                if(!mkdir($new_path, 0777, true)) {
                                    throw new Exception($this->_("Unable to create the directory."));
                                }
                            }

                            if(!rename($old_path."/".$image["filename"],$new_path."/".$image["filename"])){
                                throw new Exception($this->_("Unable to copy the file."));
                            }

                            $data_image = array(
                                "library_id" => $data["library_id"],
                                "link" => $new_path_base."/".$image["filename"],
                                "can_be_colorized" => 0,
                                "position" => $key
                            );

                            $library_image->setData($data_image)->save();

                        } else {
                            //For existing images, we save position
                            $library_image->find($image["id"]);
                            if($library_image->getImageId()) {
                                $library_image->setPosition($key)->save();
                            }
                        }

                        //We delete images to delete
                        if($image["to_delete"] == 1 AND $image["new"] == 0) {
                            if(!unlink(Core_Model_Directory::getBasePathTo($image["link"]))) {
                                throw new Exception($this->_("Unable to delete the file."));
                            }
                            $library_image = new Media_Model_Library_Image();
                            $library_image->find($image["id"]);
                            $library_image->delete();
                        }
                    }

                    unset($data["images"]);
                    //<--IMAGES------------------------------------------------
                    unset($data["from_database"]);
                    $data['language_code'] = $language_code;
                    $data['preview_id'] = $preview->getId();
                    $previews_language_data[] = $data;
                }
                $preview->setLanguageData($previews_language_data);

                $preview->save();

                $data = array(
                    "success" => 1,
                    "message" => $this->_("Preview successfully saved")
                );

            } catch(Exception $e) {
                $data = array(
                    "error" => 1,
                    "message" => $e->getMessage()
                );
            }

            $this->_sendHtml($data);
        }
    }

    public function uploadAction() {

        try {

            if(empty($_FILES) || empty($_FILES['file']['name'])) {
                throw new Exception("No file has been sent");
            }

            $base_path = Core_Model_Directory::getTmpDirectory(true);

            if(!is_dir($base_path)) {
                mkdir($base_path, 0777, true);
            }

            $params = array(
                "validators" => array(
                    "Size" => array(
                        "min" => 100,
                        "max" => 8000000
                    )
                ),
                "destination_folder" => $base_path,
                "uniq" => 1
            );

            $sizes = array("minwidth", "minheight", "maxwidth", "maxheight");
            foreach($sizes as $key) {
                if($size = $this->getRequest()->getPost($key)) {
                    $params["validators"]["ImageSize"][$key] = $size;
                }
            }

            $uploader = new Core_Model_Lib_Uploader();
            $file = $uploader->upload($params);

            if($file) {

                $this->_sendHtml(array(
                    "success" => 1,
                    "message" => $this->_("Your image has been successfully saved"),
                    "filename" => $file
                ));

            } else {
                $message = $this->_("An error occurred during the process. Please try again later.");
                throw new Exception($message);
            }
        } catch(Exception $e) {
            $data = array(
                "error" => 1,
                "message" => $e->getMessage()
            );
            $this->_sendHtml($data);
        }

    }

    public function deleteAction() {
        try {

            if ($data = Zend_Json::decode($this->getRequest()->getRawBody())) {
                $preview = new Preview_Model_Preview();
                $preview->find($data["preview_id"]);

                if($preview->getId()) {
                    $preview->deleteTranslation($data["language_code"]);
                }

                $data = array(
                    "success" => 1,
                    "message" => $this->_("Your preview translation has been deleted successfully.")
                );
                $this->_sendHtml($data);

            } else {
                throw new Exception($this->_("An error occurred while deleting your preview. Please try again later."));
            }
        } catch(Exception $e) {

            $data = array(
                "error" => 1,
                "message" => $e->getMessage()
            );
            $this->_sendHtml($data);

        }
    }
}
