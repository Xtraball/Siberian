cordova.define("siberian-plugin-offlinemode.OfflineMode", function(require, exports, module) { var exec = require('cordova/exec');

var OfflineMode = {};

OfflineMode.useCache = function(is_online, success_callback, error_callback) {
    exec(success_callback, error_callback, "OfflineMode", "useCache", [is_online]);
};

module.exports = OfflineMode;

});
