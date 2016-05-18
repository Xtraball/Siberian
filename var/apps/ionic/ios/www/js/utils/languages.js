var AVAILABLE_LANGUAGES = new Array("en");

/**
 * Find navigator preferred language
 */
var language = "en";
if(navigator.language) {
    var tmp_language = navigator.language.replace("-", "_");

    try {
        if(AVAILABLE_LANGUAGES.indexOf(tmp_language) >= 0) {
            language = tmp_language
        } else {
            language = tmp_language.split("_")[0];
        }
    } catch(e) {
        language = "en";
    }
}