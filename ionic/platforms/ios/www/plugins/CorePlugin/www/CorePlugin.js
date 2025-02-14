cordova.define("CorePlugin.CorePlugin", function(require, exports, module) {
/**
 * Core plugin
 */
var exec = require('cordova/exec');

exports.requestTrackingAuthorization = function (callback, scope) {
    this.exec('requestTrackingAuthorization', null, callback, scope);
};

exports.createCallbackFn = function (callbackFn, scope) {
    if (typeof callbackFn != 'function')
        return;

    return function () {
        callbackFn.apply(scope || this, arguments);
    };
};

exports.getTrackingAuthorizationStatus = function (callback, scope) {
    if (typeof callback != 'function')
        return;

    this.exec('getTrackingAuthorizationStatus', null, callback, scope);
}

exports.exec = function (action, args, callback, scope) {
    var fn = this.createCallbackFn(callback, scope),
        params = [];

    if (Array.isArray(args)) {
        params = args;
    } else if (args) {
        params.push(args);
    }

    exec(fn, null, 'CorePlugin', action, params);
};
});
