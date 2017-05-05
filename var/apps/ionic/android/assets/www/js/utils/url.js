/*global
 window, document, localStorage, angular
 */
var goto = window.location.hash.match(/\?__goto__=(.*)/);
var REDIRECT_URI = false;
if(goto) {
    goto = goto[1];
    var REDIRECT_URI = goto;
    /** Replace the path */
    window.location.hash = window.location.hash.replace(/\?__goto__=(.*)/, "");
}

/** Set default variables */
var is_https = (document.URL.indexOf('https') === 0);

if(document.URL.indexOf('http') === 0) {

    var BASE_PATH = "/";
    var APP_KEY = null;
    var CURRENT_LANGUAGE = null;

    var DOMAIN = window.location.protocol + "//" + window.location.host;

    if (window.location.port == 8100) {
        DOMAIN = window.location.protocol + "//www.siberiancms.dev";
    }

    var path = window.location.hash.replace("#", "").split("/").filter(Boolean);

    if(path.length > 2) {
        APP_KEY = path[0];
        path = [];
    }

    path = path.reverse();
    if (angular.isDefined(path[1])) {
        CURRENT_LANGUAGE = path[1];
        localStorage.setItem("sb-current-language", CURRENT_LANGUAGE);
    } else {
        var language = localStorage.getItem("sb-current-language");
        CURRENT_LANGUAGE = !!language ? language : "en";
    }

    if (angular.isDefined(path[0])) {
        APP_KEY = path[0];
    }

    BASE_PATH += APP_KEY;

    localStorage.setItem("sb-current-language", CURRENT_LANGUAGE);
}

var BASE_URL = DOMAIN + BASE_PATH;
var IMAGE_URL = DOMAIN + "/";