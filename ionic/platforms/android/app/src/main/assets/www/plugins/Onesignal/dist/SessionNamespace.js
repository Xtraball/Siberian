cordova.define("Onesignal.SessionNamespace", function(require, exports, module) {
"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
var Session = /** @class */ (function () {
    function Session() {
    }
    /**
     * Outcomes
     */
    /**
     * Add an outcome with the provided name, captured against the current session.
     * @param  {string} name
     * @returns void
     */
    Session.prototype.addOutcome = function (name) {
        window.cordova.exec(function () { }, function () { }, "OneSignalPush", "addOutcome", [name]);
    };
    ;
    /**
     * Add a unique outcome with the provided name, captured against the current session.
     * @param  {string} name
     * @returns void
     */
    Session.prototype.addUniqueOutcome = function (name) {
        window.cordova.exec(function () { }, function () { }, "OneSignalPush", "addUniqueOutcome", [name]);
    };
    ;
    /**
     * Add an outcome with the provided name and value, captured against the current session.
     * @param  {string} name
     * @param  {number} value
     * @returns void
     */
    Session.prototype.addOutcomeWithValue = function (name, value) {
        window.cordova.exec(function () { }, function () { }, "OneSignalPush", "addOutcomeWithValue", [name, value]);
    };
    ;
    return Session;
}());
exports.default = Session;

});
