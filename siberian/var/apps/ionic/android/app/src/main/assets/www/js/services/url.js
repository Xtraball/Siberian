/*global
    App, angular, DOMAIN, CURRENT_LANGUAGE, APP_KEY
 */
angular.module("starter").service("Url", function($location) {

    this.__sanitize = function(str) {

        if(str.startsWith("/")) {
            str = str.substr(1, str.length - 1);
        }

        return str;
    };

    var _that = this;

    return {

        get: function(uri, params) {

            if(!angular.isDefined(params)) {
                params = {};
            }

            var add_language = params.add_language;
            delete params.add_language;

            var remove_key = params.remove_key;
            delete params.remove_key;

            uri = _that.__sanitize(uri);

            var url = DOMAIN.split("/");

            if(add_language) {
                url.push(CURRENT_LANGUAGE);
            }

            if(APP_KEY && !remove_key) {
                url.push(APP_KEY);
            }

            url.push(uri);

            for(var i in params) {
                if(angular.isDefined(params[i])) {
                    url.push(i);
                    url.push(params[i]);
                }
            }

            url = url.join('/');

            return url;
        },

        build: function(uri, params) {

            if(!angular.isDefined(params)) {
                params = {};
            }

            var url = _that.__sanitize(uri);
            var p = [];

            for(var i in params) {
                if(angular.isDefined(params[i])) {
                    p.push(i+"="+params[i]);
                }
            }

            url = url + "?" + p.join("&");

            return url;
        }

    };
});

if(typeof String.prototype.startsWith !== "function") {
    String.prototype.startsWith = function (str) {
        return this.substring(0, str.length) === str;
    };
}

if(typeof String.prototype.endsWith !== "function") {
    String.prototype.endsWith = function (str) {
        return this.substring(this.length - str.length, this.length) === str;
    };
}