<?php

class Translation_Backoffice_EditController extends Backoffice_Controller_Default
{

    protected $_xml_files;

    public function preDispatch() {
        $this->_xml_files = array(
            "android-app.xml" => array(
                "is_translatable" => true,
                "name" => "android-app.xml",
                "base_path" => Core_Model_Directory::getBasePathTo("var/apps/angular/android/Siberian/app/src/main/res/values/"),
                "user_path" => Core_Model_Directory::getBasePathTo("var/apps/angular/android/Siberian/app/src/main/res/"),
                "file_name" => "strings.xml",
                "info" => $this->_("Translate the 'e.g.' between parentheses only.")
            ),
            "android-ionic-app.xml" => array(
                "is_translatable" => true,
                "name" => "android-ionic-app.xml",
                "base_path" => Core_Model_Directory::getBasePathTo("var/apps/ionic/android/res/values/"),
                "user_path" => Core_Model_Directory::getBasePathTo("var/apps/ionic/android/res/"),
                "file_name" => "strings.xml",
                "info" => $this->_("Translate the 'e.g.' between parentheses only.")
            ),
            "android-previewer.xml" => array(
                "is_translatable" => Installer_Model_Installer::hasModule('Previewer'),
                "name" => "android-previewer.xml",
                "base_path" => Core_Model_Directory::getBasePathTo("var/apps/ionic/previewer/android/res/values/"),
                "user_path" => Core_Model_Directory::getBasePathTo("var/apps/ionic/previewer/android/res/"),
                "file_name" => "strings.xml",
                "info" => $this->_("Translate the 'e.g.' between parentheses only.")
            ),
        );
        parent::preDispatch();
    }

    public function loadAction() {

        $html = array(
            "title" => $this->_("Translations"),
            "icon" => "fa-language",
        );

        $this->_sendHtml($html);

    }

    public function findAction() {

        $data = $data_csv = $data_all = array();
        if($lang_id = $this->getRequest()->getParam("lang_id")) {

            $lang_id = base64_decode($lang_id);
            $lang_id = explode("_", strtolower($lang_id));
            if(count($lang_id) == 2) {
                $lang_id[1] = strtoupper($lang_id[1]);
            }
            $lang_id = implode("_", $lang_id);

            $data["section_title"] = $this->_("Edit the language: %s", Core_Model_Language::getLanguage($lang_id)->getName());
            $data["is_edit"] = true;

        } else {
            $data["section_title"] = $this->_("Create a new language");
            $data["is_edit"] = false;
        }
        $data["country_code"] = $lang_id;

        $locale = Zend_Registry::get("Zend_Locale");
        $languages = $locale->getTranslationList('language');
        $existing_languages = Core_Model_Language::getLanguageCodes();
        foreach($languages as $k => $language) {
            if(!$locale->isLocale($k) OR in_array($k, $existing_languages)) {
                unset($languages[$k]);
            }
        }

        asort($languages, SORT_LOCALE_STRING);
        $data["country_codes"] = $languages;

        $data_csv = $this->_parseCsv($lang_id);
        $data_xml = $this->_parseXml($lang_id);

        $data_all["translation_files"] = array_merge($data_csv["translation_files"], $data_xml["translation_files"]);
        $data_all["translation_files_data"] = array_merge($data_csv["translation_files_data"], $data_xml["translation_files_data"]);

        ksort($data_all["translation_files"]);
        $data["translation_files"] = $data_all["translation_files"];
        ksort($data_all["translation_files_data"]);
        $data["translation_files_data"] = $data_all["translation_files_data"];

        $data["info"] = array_merge($data_csv["info"], $data_xml["info"]);

        $this->_sendHtml($data);

    }

    public function saveAction() {

        if($data = Zend_Json::decode($this->getRequest()->getRawBody())) {

            try {

                $base_path = Core_Model_Directory::getBasePathTo("languages/");
                $country_code = $data["country_code"];
                $translation_dir = $base_path.$country_code;
                $translation_file = $data["file"];
                $translation_datas = $data["collection"];
                ksort($translation_datas);

                if(empty($country_code)) throw new Exception($this->_("Please, choose a language."));
                if(empty($translation_file) ) throw new Exception($this->_("Please, choose a file."));

                //android translations
                $pathinfo = pathinfo($translation_file);
                if(!empty($pathinfo["extension"]) AND $pathinfo["extension"] == "xml") {

                    $base_path = $this->_xml_files[$translation_file]["base_path"];
                    $default_translation_dir = $base_path;
                    $translation_dir = $this->_xml_files[$translation_file]["user_path"]."values-".Core_Model_Lib_String::formatLanguageCodeForAndroid($country_code).DS;
                    $translation_file = $this->_xml_files[$translation_file]["file_name"];
                    if(!is_dir($translation_dir)) {
                        mkdir($translation_dir);
                    }

                    $xml = new DOMDocument();
                    $xml->load($default_translation_dir.$translation_file);

                    foreach($translation_datas as $key => $value) {

                        $modified_key = str_replace(" ", "_", strtolower(trim(current(explode("(", $key)))));
                        foreach($xml->getElementsByTagName("string") as $node) {
                            if( (empty($value) && $node->getAttribute("name") == $modified_key) || stripos($node->nodeValue, '{#--') !== false) {
                                $node->parentNode->removeChild($node);
                                continue;
                            }

                            if($node->getAttribute("name") == $modified_key) {
                                $cdata = $xml->createCDataSection(addslashes($value));

                                $node->nodeValue = "";
                                $node->appendChild($cdata);
                            }
                        }
                    }

                    $xml->save($translation_dir.$translation_file);
                } else {

                    if (!is_dir($translation_dir)) {
                        mkdir($translation_dir);
                    }

                    $ressource = fopen($translation_dir . DS . $translation_file, "w");
                    foreach($translation_datas as $key => $value) {
                        if(empty($value)) continue;
                        $this->_putCsv($ressource, array($key, $value));
                    }
                    fclose($ressource);

                    if(!file_exists($translation_dir . DS . "default.csv")) {
                        $ressource = fopen($translation_dir . DS . "default.csv", "w");
                        $this->_putCsv($ressource, array("", ""));
                        fclose($ressource);
                    }
                }

                $data = array(
                    "success" => 1,
                    "message" => $this->_("Language successfully saved")
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

    protected function _parseCsv($lang_id) {

        $data = $translation_files_data = $translation_files = array();

        $user_translation_dir = Core_Model_Directory::getBasePathTo("languages/".$lang_id.DS);
        $default_base_path = Core_Model_Directory::getBasePathTo("languages/default");

        $files = new DirectoryIterator($default_base_path);
        foreach($files as $file) {

            if($file->isDot()) continue;
            $pathinfo = pathinfo($file);
            if(empty($pathinfo["extension"]) OR $pathinfo["extension"] != "csv") continue;

            $translation_files[$file->getFilename()] = $file->getFilename();

            $resource = fopen($file->getRealPath(), "r");
            $translation_files_data[$file->getFilename()] = array();
            while($content = fgetcsv($resource, 1024, ";", '"')) {
                $translation_files_data[$file->getFilename()][$content[0]] = null;
            }
            fclose($resource);

            if(!is_file($user_translation_dir.$file->getFilename())) continue;

            $resource = fopen($user_translation_dir.$file->getFilename(), "r");
            while($content = fgetcsv($resource, 1024, ";", '"')) {
                $translation_files_data[$file->getFilename()][$content[0]] = $content[1];
                asort($translation_files_data[$file->getFilename()]);
            }

            fclose($resource);
        }

        $data["translation_files"] = $translation_files;
        $data["translation_files_data"] = $translation_files_data;
        $data["info"] = array();

        return ($data);
    }

    protected function _parseXml($lang_id) {

        $data = $translation_files_data = $translation_files = array();

        $lang_id = Core_Model_Lib_String::formatLanguageCodeForAndroid($lang_id);

        foreach($this->_xml_files as $file) {


            if($file["is_translatable"]) {

                $file_name = $file["name"];
                $user_translation_dir = $file["user_path"]."values-".$lang_id.DS;
                $default_base_path = $file["base_path"];
                $translation_file_name = $file["file_name"];
                $default_file = $default_base_path . $translation_file_name;
                $user_file = $user_translation_dir . $translation_file_name;

                $pathinfo = pathinfo($default_file);
                if (empty($pathinfo["extension"]) OR $pathinfo["extension"] != "xml") return;

                $translation_files[$file_name] = $file_name;

                $file_xml_data = simplexml_load_file($default_file);
                $user_file_xml_data = null;
                if (is_file($user_file)) {
                    $user_file_xml_data = simplexml_load_file($user_file);
                }

                $i = 0;
                foreach ($file_xml_data->children() as $string) {

                    if (!in_array((string) $string->attributes()->name, array("app_name", "url")) AND stripos((string) $string, "@string/") === false) {

                        if(stripos((string) $string, '{#--') !== false) continue;

                        $key = (str_replace("_", " ", ucfirst((string)$string->attributes()->name))) . " (e.g.: " . (string) $string . ")";

                        if ($user_file_xml_data != null) {

                            foreach($user_file_xml_data as $user_string) {
                                if((string) $string->attributes()->name == (string) $user_string->attributes()->name) {
                                    $translation_files_data[$file_name][$key] = stripslashes((string) $user_string);
                                    break;
                                } else {
                                    $translation_files_data[$file_name][$key] = null;
                                }
                            }

                        } else {
                            $translation_files_data[$file_name][$key] = null;
                        }
                    }
                    $i++;
                }

                $data["translation_files"] = $translation_files;
                $data["translation_files_data"] = $translation_files_data;
                $data["info"][$file_name] = $file["info"];

            }
        }

        return ($data);
    }

    protected function _putCsv($resource, $data) {

        $enclosure = '"';
        $separator = ';';
        $br = "\n";

        $str = array(
            $enclosure,
            str_replace($enclosure, $enclosure.$enclosure, $data[0]),
            $enclosure,
            $separator,
            $enclosure,
            str_replace($enclosure, $enclosure.$enclosure, $data[1]),
            $enclosure,
            $br
        );

        fputs($resource, join("", $str));

    }

}
