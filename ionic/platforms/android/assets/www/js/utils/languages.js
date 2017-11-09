/* global
 navigator, getParameterByName
 */

var AVAILABLE_LANGUAGES = ['en'];
var language = 'en';
var forceLocale = getParameterByName('locale');
if (AVAILABLE_LANGUAGES.indexOf(forceLocale) >= 0) {
    // Force locale via param
    language = forceLocale;
} else if (navigator.language) {
    var tmp_language = navigator.language.replace('-', '_');

    try {
        if (AVAILABLE_LANGUAGES.indexOf(tmp_language) >= 0) {
            language = tmp_language;
        } else {
            language = tmp_language.split('_')[0];
        }
    } catch (e) {
        language = 'en';
    }
}
