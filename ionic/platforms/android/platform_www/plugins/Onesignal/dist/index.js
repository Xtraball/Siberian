cordova.define("Onesignal.OneSignalPlugin", function(require, exports, module) {
"use strict";
/**
 * Modified MIT License
 *
 * Copyright 2019 OneSignal
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * 1. The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * 2. All copies of substantial portions of the Software may only be used in connection
 * with services provided by OneSignal.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.NotificationWillDisplayEvent = exports.OSNotificationPermission = exports.OSNotification = exports.LogLevel = exports.OneSignalPlugin = void 0;
var UserNamespace_1 = __importDefault(require("./UserNamespace"));
var DebugNamespace_1 = __importDefault(require("./DebugNamespace"));
var SessionNamespace_1 = __importDefault(require("./SessionNamespace"));
var LocationNamespace_1 = __importDefault(require("./LocationNamespace"));
var InAppMessagesNamespace_1 = __importDefault(require("./InAppMessagesNamespace"));
var NotificationsNamespace_1 = __importDefault(require("./NotificationsNamespace"));
var LiveActivitiesNamespace_1 = __importDefault(require("./LiveActivitiesNamespace"));
var OneSignalPlugin = /** @class */ (function () {
    function OneSignalPlugin() {
        this.User = new UserNamespace_1.default();
        this.Debug = new DebugNamespace_1.default();
        this.Session = new SessionNamespace_1.default();
        this.Location = new LocationNamespace_1.default();
        this.InAppMessages = new InAppMessagesNamespace_1.default();
        this.Notifications = new NotificationsNamespace_1.default();
        this.LiveActivities = new LiveActivitiesNamespace_1.default();
        this._appID = "";
    }
    /**
     * Initializes the OneSignal SDK. This should be called during startup of the application.
     * @param  {string} appId
     * @returns void
     */
    OneSignalPlugin.prototype.initialize = function (appId) {
        var _this = this;
        this._appID = appId;
        var observerCallback = function () {
            _this.User.pushSubscription._setPropertiesAndObserver();
            _this.Notifications._setPropertyAndObserver();
        };
        window.cordova.exec(observerCallback, function () { }, "OneSignalPush", "init", [this._appID]);
    };
    ;
    /**
     * Login to OneSignal under the user identified by the [externalId] provided. The act of logging a user into the OneSignal SDK will switch the [user] context to that specific user.
     * @param  {string} externalId
     * @returns void
     */
    OneSignalPlugin.prototype.login = function (externalId) {
        window.cordova.exec(function () { }, function () { }, "OneSignalPush", "login", [externalId]);
    };
    /**
     * Logout the user previously logged in via [login]. The [user] property now references a new device-scoped user.
     * @param  {string} externalId
     * @returns void
     */
    OneSignalPlugin.prototype.logout = function () {
        window.cordova.exec(function () { }, function () { }, "OneSignalPush", "logout");
    };
    /**
      * Determines whether a user must consent to privacy prior to their user data being sent up to OneSignal. This should be set to true prior to the invocation of initialization to ensure compliance.
      * @param  {boolean} required
      * @returns void
      */
    OneSignalPlugin.prototype.setConsentRequired = function (required) {
        window.cordova.exec(function () { }, function () { }, "OneSignalPush", "setPrivacyConsentRequired", [required]);
    };
    ;
    /**
     * Indicates whether privacy consent has been granted. This field is only relevant when the application has opted into data privacy protections.
     * @param  {boolean} granted
     * @returns void
     */
    OneSignalPlugin.prototype.setConsentGiven = function (granted) {
        window.cordova.exec(function () { }, function () { }, "OneSignalPush", "setPrivacyConsentGiven", [granted]);
    };
    ;
    return OneSignalPlugin;
}());
exports.OneSignalPlugin = OneSignalPlugin;
//-------------------------------------------------------------------
var OneSignal = new OneSignalPlugin();
if (!window.plugins) {
    window.plugins = {};
}
if (!window.plugins.OneSignal) {
    window.plugins.OneSignal = OneSignal;
}
// Exporting
var DebugNamespace_2 = require("./DebugNamespace");
Object.defineProperty(exports, "LogLevel", { enumerable: true, get: function () { return DebugNamespace_2.LogLevel; } });
var OSNotification_1 = require("./OSNotification");
Object.defineProperty(exports, "OSNotification", { enumerable: true, get: function () { return OSNotification_1.OSNotification; } });
var NotificationsNamespace_2 = require("./NotificationsNamespace");
Object.defineProperty(exports, "OSNotificationPermission", { enumerable: true, get: function () { return NotificationsNamespace_2.OSNotificationPermission; } });
var NotificationReceivedEvent_1 = require("./NotificationReceivedEvent");
Object.defineProperty(exports, "NotificationWillDisplayEvent", { enumerable: true, get: function () { return NotificationReceivedEvent_1.NotificationWillDisplayEvent; } });
exports.default = OneSignal;

});
