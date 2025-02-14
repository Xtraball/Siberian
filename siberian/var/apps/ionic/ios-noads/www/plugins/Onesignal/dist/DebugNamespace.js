cordova.define("Onesignal.DebugNamespace", function(require, exports, module) {
"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.LogLevel = void 0;
// An enum that declares different types of log levels you can use with the OneSignal SDK, going from the least verbose (none) to verbose (print all comments).
var LogLevel;
(function (LogLevel) {
    LogLevel[LogLevel["None"] = 0] = "None";
    LogLevel[LogLevel["Fatal"] = 1] = "Fatal";
    LogLevel[LogLevel["Error"] = 2] = "Error";
    LogLevel[LogLevel["Warn"] = 3] = "Warn";
    LogLevel[LogLevel["Info"] = 4] = "Info";
    LogLevel[LogLevel["Debug"] = 5] = "Debug";
    LogLevel[LogLevel["Verbose"] = 6] = "Verbose";
})(LogLevel = exports.LogLevel || (exports.LogLevel = {}));
var Debug = /** @class */ (function () {
    function Debug() {
    }
    /**
     * Enable logging to help debug if you run into an issue setting up OneSignal.
     * @param  {LogLevel} logLevel - Sets the logging level to print to the Android LogCat log or Xcode log.
     * @returns void
     */
    Debug.prototype.setLogLevel = function (logLevel) {
        window.cordova.exec(function () { }, function () { }, "OneSignalPush", "setLogLevel", [logLevel]);
    };
    ;
    /**
     * Enable logging to help debug if you run into an issue setting up OneSignal.
     * @param  {LogLevel} visualLogLevel - Sets the logging level to show as alert dialogs.
     * @returns void
     */
    Debug.prototype.setAlertLevel = function (visualLogLevel) {
        window.cordova.exec(function () { }, function () { }, "OneSignalPush", "setAlertLevel", [visualLogLevel]);
    };
    return Debug;
}());
exports.default = Debug;

});
