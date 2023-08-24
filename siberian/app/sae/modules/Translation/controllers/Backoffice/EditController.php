<?php

use Gettext\Translation;
use Gettext\Translations;
use Siberian\File;

/**
 * Class Translation_Backoffice_EditController
 */
class Translation_Backoffice_EditController extends Backoffice_Controller_Default
{
    /**
     * @var
     */
    protected $_xml_files;

    /**
     * Default tree
     */
    public function loadAction()
    {
        $payload = [
            'title' => sprintf('%s > %s',
                __('Settings'),
                __('Translations')),
            'icon' => 'fa-language',
        ];

        $this->_sendJson($payload);
    }

    public function findAction()
    {
        try {
            $request = $this->getRequest();
            $isEdit = true;

            $langId = $request->getParam("langId", null);
            if (empty($langId)) {
                $sectionTitle = __("Create a new language");
                $isEdit = false;
            } else {
                $langId = base64_decode($langId);
                $sectionTitle = __("Edit the language: %s",
                    Core_Model_Language::getLanguage($langId)->getName());
            }

            $countryCode = $langId;

            $locale = Zend_Registry::get("Zend_Locale");
            $languages = $locale->getTranslationList("language");
            $existingLanguages = Core_Model_Language::getLanguageCodes();
            foreach ($languages as $k => $language) {
                if (!$locale->isLocale($k) || in_array($k, $existingLanguages)) {
                    unset($languages[$k]);
                }
            }

            asort($languages, SORT_LOCALE_STRING);

            // Parsing .mo base files!
            $translations = $this->parseTranslations($langId);

            // Available files
            $files = [];
            foreach (array_keys($translations) as $file) {
                $baseName = basename(basename($file, ".csv"), ".po");
                if (strpos($baseName, "c_") === 0) {
                    $_tmp = str_replace("c_", "[Context] ", $baseName);
                    $_tmp = str_replace("_", " > ", $_tmp);
                    $_tmp = str_replace("-", " > ", $_tmp);
                } else {
                    $_tmp = ucfirst($baseName);
                    $_tmp = str_replace("_", " ", $_tmp);
                    $_tmp = str_replace("-", " ", $_tmp);
                }
                $files[$file] = $_tmp;
            }

            $merged = [];
            foreach ($translations as $file => $keyValues) {
                $baseName = basename(basename($file, ".csv"), ".po");
                if (strpos($baseName, "c_") === 0) {
                    $_tmp = str_replace("c_", "[Context] ", $baseName);
                    $_tmp = str_replace("_", " > ", $_tmp);
                    $_tmp = str_replace("-", " > ", $_tmp);
                } else {
                    $_tmp = ucfirst($baseName);
                    $_tmp = str_replace("_", " ", $_tmp);
                    $_tmp = str_replace("-", " ", $_tmp);
                }

                foreach ($keyValues as $key => $value) {
                    $merged[] = [
                        "file" => $file,
                        "title" => $_tmp,
                        "key" => $key,
                        "value" => $value,
                        "search" => sprintf("%s %s %s %s %s", $_tmp, $key, $value['original'], $value['default'], $value['user'])
                    ];
                }
            }

            $payload = [
                "success" => true,
                "section_title" => $sectionTitle,
                "is_edit" => $isEdit,
                "country_code" => $countryCode,
                "country_codes" => $languages,
                "translation_files" => $files,
                "translations" => $translations,
                "search_context" => array_values($merged),
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
    public function saveAction()
    {
        try {
            $request = $this->getRequest();
            $data = $request->getBodyParams();

            if (__getConfig("is_demo")) {
                // Demo version
                throw new \Siberian\Exception(__("You cannot change translation, this is a demo version."));
            }

            if (empty($data)) {
                throw new \Siberian\Exception(__("Missing data, unable to save!"));
            }

            $base_path = Core_Model_Directory::getBasePathTo("languages/");
            $countryCode = $data["country_code"];
            $translationDir = $base_path . $countryCode;
            $translationFile = $data["file"];
            $translationData = $data["collection"];
            ksort($translationData);

            if (empty($countryCode)) {
                throw new \Siberian\Exception(__("Please, choose a language."));
            }
            if (empty($translationFile)) {
                throw new \Siberian\Exception(__("Please, choose a file."));
            }

            if (!is_dir($translationDir)) {
                mkdir($translationDir);
            }

            // Yeah!
            $translations = new Translations();
            foreach ($translationData as $key => $values) {
                $context = trim($values["context"]);
                $flags = $values["flags"];
                $comments = $values["comments"];
                $originalValue = trim($values["original"]);
                $defaultValue = trim($values["default"]);
                $userValue = trim($values["user"]);

                // Saving only filled user values & if different from default on production!
                $shouldSave = $defaultValue != $userValue;

                // In development, we do save ALL entries!
                if (isDev()) {
                    $shouldSave = true;
                }

                if (!empty($userValue) && $shouldSave) {
                    $tmp = new Translation(null, $originalValue);
                    if (!empty($flags)) {
                        foreach ($flags as $flag) {
                            $tmp->addFlag($flag);
                        }
                    }
                    if (!empty($comments)) {
                        foreach ($comments as $comment) {
                            $tmp->addComment($comment);
                        }
                    }
                    if (!empty($context)) {
                        $tmp->setContext($context);
                    }
                    $tmp->setTranslation($userValue);

                    $translations[] = $tmp;
                }
            }

            $translationFile = str_replace(".csv", ".po", $translationFile);
            $translations->toPoFile("{$translationDir}/$translationFile");

            # Clean "*_translation" cache tags
            $this->cache->clean(
                Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG,
                [
                    "mobile_translation"
                ]
            );

            $payload = [
                "success" => true,
                "message" => __("Language successfully saved"),
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
     * @param $langId
     * @return array
     * @throws Zend_Translate_Exception
     */
    public function parseTranslations($langId)
    {
        return Core_Model_Translator::parseTranslationsForBackoffice($langId);
    }

    /**
     * @param $resource
     * @param $data
     */
    protected function _putCsv($resource, $data)
    {
        $enclosure = '"';
        $separator = ';';
        $br = "\n";

        // Fix reverse addcslashes, and re-add them just in case of!
        $key = addcslashes(str_replace('\"', '"', $data[0]), '"');
        $value = addcslashes(str_replace('\"', '"', $data[1]), '"');

        $str = [
            $enclosure,
            $key,
            $enclosure,
            $separator,
            $enclosure,
            $value,
            $enclosure,
            $br
        ];

        fputs($resource, join("", $str));
    }

    /**
     *
     */
    public function translateAction()
    {
        $api = Api_Model_Key::findKeysFor("yandex");
        $yandex_key = $api->getApiKey();

        $data = Siberian_Json::decode($this->getRequest()->getRawBody());


        /** Caching */
        $translation = new Cache_Model_Translation();
        $translation = $translation->find([
            "target" => $data["target"],
            "text" => $data["text"],
        ]);

        if ($translation->getId()) {
            $html = [
                "success" => 1,
                "from_cache" => 1,
                "result" => ["text" => [$translation->getTranslation()]],
            ];
        } else {
            if (empty($yandex_key)) {
                $html = [
                    "error" => 1,
                    "message" => __("#734-01: Missing yandex API key"),
                ];
            } else {
                $url = "https://translate.yandex.net/api/v1.5/tr.json/translate?key=" . $yandex_key . "&text=%TEXT%&lang=en-%TARGET%";
                $url = str_replace("%TEXT%", urlencode($data["text"]), $url);
                $url = str_replace("%TARGET%", urlencode($data["target"]), $url);

                $response = Siberian_Json::decode(file_get_contents($url));

                if (isset($response["code"]) && $response["code"] == "200") {
                    $translation
                        ->setTarget($data["target"])
                        ->setText($data["text"])
                        ->setTranslation($response["text"][0])
                        ->save();

                    $html = [
                        "succes" => 1,
                        "result" => $response,
                    ];
                } else {
                    /** Try with google */
                    $url = "https://translate.googleapis.com/translate_a/single?client=gtx&sl=en&tl=%TARGET%&dt=t&q=%TEXT%";
                    $url = str_replace("%TEXT%", urlencode($data["text"]), $url);
                    $url = str_replace("%TARGET%", urlencode($data["target"]), $url);

                    $response = Siberian_Json::decode(file_get_contents($url));
                    $result = $response[0][0][0];

                    if (!empty($result)) {
                        $translation
                            ->setTarget($data["target"])
                            ->setText($data["text"])
                            ->setTranslation($result)
                            ->save();

                        $html = [
                            "succes" => 1,
                            "result" => ["text" => [$result]],
                        ];
                    } else {
                        $html = [
                            "error" => 1,
                            "message" => (isset($response["message"])) ? "#734-02: " . __($response["message"]) : __("#734-03: Invalid yandex API key OR Free limit request exceeded."),
                        ];
                    }

                }

            }
        }

        $this->_sendHtml($html);
    }

    public function suggestAction ()
    {
        try {
            $request = $this->getRequest();
            $data = $request->getBodyParams();

            $component = strtolower(str_replace([".mo", ".csv", ".po"], "", $data['file']));
            $language = base64_decode($data['langId']);
            $original = $data['original'];
            $user = $data['user'];

            $fileContent = [
                $original => $user,
            ];

            $tmpFile = path("/var/tmp/" . uniqid() . ".json");
            File::putContents($tmpFile, json_encode($fileContent));

            // Public API
            Siberian_Request::$debug = true;
            Siberian_Request::post(
                "https://translate.siberiancms.com/api/translations/siberian/{$component}/{$language}/file/",
                [
                    "method" => "suggest",
                    "file" => curl_file_create($tmpFile),
                ],
                null,
                null,
                [
                    "Authorization: Token lPoOy7AGUpFGbYyIVXQly1cd2mdHDVw6o6tkZTA9",
                ],
                [
                    "json_body" => true,
                ]
            );

            // Clean-up
            unlink($tmpFile);

            $payload = [
                "success" => true,
                "message" => __("Your suggestion has been sent for review, thank you."),
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
