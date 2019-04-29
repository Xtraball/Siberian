<?php

use Siberian\Exception;
use Siberian\Minify;

/**
 * Class Application_Model_Pwa
 */
class Application_Model_Pwa
{
    /**
     * @param Application_Model_Application $application
     * @throws Exception
     * @throws \rock\sanitize\SanitizeException
     */
    public static function generate (Application_Model_Application $application)
    {
        $s = microtime(true);

        $browser = path("var/apps/browser");
        $appKey = $application->getKey();
        $appName = $application->getName();

        if (!preg_match("/^([a-zA-Z0-9\-]+)$/", $appKey)) {
            throw new Exception(p__("application", "The application key is invalid."));
        }

        // Copy browser to public/appKey
        $pwaPath = path("public/{$appKey}");
        if (is_dir($pwaPath)) {
            exec("rm -rf {$pwaPath}/*");
        }

        // Ensure folders exists/is created
        mkdir($pwaPath, 0777, true);

        // Copy assets
        exec("cp -r {$browser}/* {$pwaPath}/");

        // Generate url.js / languages.js
        $languagesJs = path("public/{$appKey}/js/utils/languages.js");
        $languages = array_keys(Core_Model_Language::getLanguages());
        $arrLang = implode('","', $languages);
        $languagesContent = <<<JS
/** "public/{$appKey}/js/utils/languages.js" */
var AVAILABLE_LANGUAGES = ["{$arrLang}"];
var language = "en";
if(navigator.language) {
    var tmp_language = navigator.language.replace("-", "_");

    try {
        if(AVAILABLE_LANGUAGES.indexOf(tmp_language) >= 0) {
            language = tmp_language;
        } else {
            language = tmp_language.split("_")[0];
        }
    } catch(e) {
        language = "en";
    }
}
JS;
        file_put_contents($languagesJs, $languagesContent);

        $urlJs = path("public/{$appKey}/js/utils/url.js");
        $mainDomain = __get("main_domain");
        $urlContent = <<<JS
/** "public/{$appKey}/js/utils/url.js" */
var REDIRECT_URI = false;
var IS_NATIVE_APP = false;
var DEVICE_TYPE = 3;
var CURRENT_LANGUAGE = AVAILABLE_LANGUAGES.indexOf(language) >= 0 ? language : 'en';
var PROTOCOL = "https";
var DOMAIN = "https://{$mainDomain}";
var APP_KEY = "{$appKey}";
var BASE_PATH = "/";
var BASE_URL = DOMAIN + BASE_PATH;
var IMAGE_URL = DOMAIN + "/";
JS;
        file_put_contents($urlJs, $urlContent);

        // Enable HTML5 Mode
        $appMinJs = path("public/{$appKey}/dist/app.min.js");
        __replace(
            ['window.pwaHtml5="#"' => '$locationProvider.html5Mode(true);'],
            $appMinJs);

        $minifier = new Minify();
        $minifier->buildPwa($application);

        // Append <base href="/public/appKey">
        $indexHtml = path("public/{$appKey}/index.html");
        __replace(
            [
                "/<base href=\"\">/mi" => "<base href=\"/public/{$appKey}/\">",
                "/<title><\/title>/mi" => "<title>{$appName}</title>"
            ],
            $indexHtml,
            true);

        dbg(microtime(true) - $s);
    }
}