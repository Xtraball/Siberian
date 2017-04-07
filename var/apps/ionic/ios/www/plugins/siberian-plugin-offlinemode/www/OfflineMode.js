cordova.define("siberian-plugin-offlinemode.OfflineMode", function(require, exports, module) {
var exec = require('cordova/exec');

var OfflineMode = function() {
    var offline_mode = {};

    var callbacks = [];

    offline_mode.setCheckConnectionURL = function(checkConnectionURL, success_callback, error_callback) {
        exec(success_callback, error_callback, "OfflineMode", "setCheckConnectionURL", [checkConnectionURL]);
    };

    offline_mode.cacheURL = function(url, success_callback, error_callback) {
        exec(success_callback, error_callback, "OfflineMode", "cacheURL", [url]);
    };

    offline_mode.setCanCache = function() {
        exec(function() {}, function() {}, "OfflineMode", "setCanCache", []);
    };

    var internalCallbackName = "__internal_callback_"+(+(new Date()));
    offline_mode[internalCallbackName] = function (data) {
        for(var i = callbacks.length - 1; i >= 0; i--) {
            callbacks[i](data);
        }
    };

    exec(
        offline_mode[internalCallbackName],
        function() { console.log("[offline-mode] error (should never be called)"); },
         "OfflineMode",
         "setInternalCallback",
         []
    );

    offline_mode.registerCallback = function(callback) {
        if((typeof callback === "function")) {
            callbacks.push(callback);
            return offline_mode.unregisterCallback.bind(offline_mode, callback);
        }

        return false;
    };

    offline_mode.unregisterCallback = function(callback) {
        for(var i = callbacks.length - 1; i >= 0; i--) {
            if(callbacks[i] === callback) {
                callbacks.splice(i, 1);
            }
        }
    };

    return offline_mode;
};

module.exports = new OfflineMode();

});
