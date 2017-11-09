/*global
 window, document, localStorage, angular
 */
var goto = window.location.hash.match(/\?__goto__=(.*)/);
var REDIRECT_URI = false;
var fbtoken = window.location.hash.match(/\?__tokenfb__=(.*)/);
var LOGIN_FB = false;
var IS_NATIVE_APP = false;
var DEVICE_TYPE = 3;

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

/** Set default variables */
var is_https = (document.URL.indexOf('https') === 0);

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
    var CURRENT_LANGUAGE = null;

    var DOMAIN = window.location.protocol + '//' + window.location.host;

    if ((window.location.port * 1) === 8100) {
        checkDevDomain();
    }

    var path = window.location.hash.replace('#', '').split('/').filter(Boolean);


    if (path.length > 2) {
        APP_KEY = path[0];
        path = [];
    }

    path = path.reverse();
    if (angular.isDefined(path[1])) {
        CURRENT_LANGUAGE = path[1];
        localStorage.setItem('sb-current-language', CURRENT_LANGUAGE);
    } else {
        var language = localStorage.getItem('sb-current-language');
        CURRENT_LANGUAGE = !!language ? language : 'en';
    }

    if (angular.isDefined(path[0])) {
        APP_KEY = path[0];
    }

    if ((APP_KEY === null) || (APP_KEY === '')) {
        APP_KEY = getParameterByName('app_key');
    }

    BASE_PATH += APP_KEY;

    localStorage.setItem('sb-current-language', CURRENT_LANGUAGE);
}

var BASE_URL = DOMAIN + BASE_PATH;
var IMAGE_URL = DOMAIN + '/';


function checkDevDomain() {
    var dev_domain = localStorage.getItem("dev_domain");
    while(typeof dev_domain !== "string" || !(/https?:\/\//i.test(dev_domain))) {
        dev_domain = prompt("Enter your siberiancms dev domain, beginning with https");
        if(dev_domain === null) {
            window.setTimeout(function() {
                if(!window.refreshAfterSet) {
                    var remove = function(i) { return typeof i === "object" && typeof i.remove === "function" && i.remove(); };
                    document.getElementsByName("html").forEach(remove);
                    document.getElementsByName("head").forEach(remove);
                    document.getElementsByName("body").forEach(remove);

                    document.write("<html><button onclick=\"checkDevDomain()\">Please specify your dev domain.</button></html>");
                    window.refreshAfterSet = true;
                }
            }, 500);
            throw "Please enter your dev domain.";
        }
    }
    DOMAIN = dev_domain;
    localStorage.setItem("dev_domain", DOMAIN);
    if(window.refreshAfterSet) {
        window.location.reload();
    }
}
