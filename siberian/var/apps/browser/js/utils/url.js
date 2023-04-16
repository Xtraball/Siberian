/*global
 window, document, localStorage, angular
 */
var goto = window.location.hash.match(/\?__goto__=(.*)/);
var HASH_ON_START = window.location.hash;
var REDIRECT_URI = false;
var fbtoken = window.location.hash.match(/\?__tokenfb__=(.*)/);
var LOGIN_FB = false;
var IS_NATIVE_APP = false;
var DEVICE_TYPE = 3;
var XS_VERSION = false;
var AVAILABLE_LANGUAGES = ['en'];
var DISABLE_BATTERY_OPTIMIZATION = false;

if (goto) {
    goto = goto[1];
    REDIRECT_URI = goto;
    // Replace the path!
    window.location.hash = window.location.hash.replace(/\?__goto__=(.*)/, '');
}

if (fbtoken) {
    LOGIN_FB = true;
    fbtoken = fbtoken[1];
    // Replace the path!
    window.location.hash = window.location.hash.replace(/\?__tokenfb__=(.*)/, '');
}

function getParameterByName(name, url) {
    if (!url) {
        url = window.location.href;
    }
    name = name.replace(/[\[\]]/g, "\\$&");
    var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
        results = regex.exec(url);
    if (!results) {
        return null;
    }
    if (!results[2]) {
        return '';
    }
    return decodeURIComponent(results[2].replace(/\+/g, ' '));
}

if (document.URL.indexOf('http') === 0) {
    var BASE_PATH = '/';
    var APP_KEY = null;

    var DOMAIN = window.location.protocol + '//' + window.location.host;
    var path = window.location.hash.replace('#', '').split('/').filter(Boolean);

    if (path.length > 2) {
        APP_KEY = path[0];
        path = [];
    }

    path = path.reverse();

    if (angular.isDefined(path[0])) {
        APP_KEY = path[0];
    }

    if ((APP_KEY === null) || (APP_KEY === '')) {
        APP_KEY = getParameterByName('app_key');
    }

    BASE_PATH += APP_KEY;
}

var BASE_URL = DOMAIN + BASE_PATH;
var IMAGE_URL = DOMAIN + '/';
