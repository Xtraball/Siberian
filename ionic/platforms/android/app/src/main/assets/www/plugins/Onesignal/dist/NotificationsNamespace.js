cordova.define("Onesignal.NotificationsNamespace", function(require, exports, module) {
"use strict";
var __awaiter = (this && this.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
var __generator = (this && this.__generator) || function (thisArg, body) {
    var _ = { label: 0, sent: function() { if (t[0] & 1) throw t[1]; return t[1]; }, trys: [], ops: [] }, f, y, t, g;
    return g = { next: verb(0), "throw": verb(1), "return": verb(2) }, typeof Symbol === "function" && (g[Symbol.iterator] = function() { return this; }), g;
    function verb(n) { return function (v) { return step([n, v]); }; }
    function step(op) {
        if (f) throw new TypeError("Generator is already executing.");
        while (g && (g = 0, op[0] && (_ = 0)), _) try {
            if (f = 1, y && (t = op[0] & 2 ? y["return"] : op[0] ? y["throw"] || ((t = y["return"]) && t.call(y), 0) : y.next) && !(t = t.call(y, op[1])).done) return t;
            if (y = 0, t) op = [op[0] & 2, t.value];
            switch (op[0]) {
                case 0: case 1: t = op; break;
                case 4: _.label++; return { value: op[1], done: false };
                case 5: _.label++; y = op[1]; op = [0]; continue;
                case 7: op = _.ops.pop(); _.trys.pop(); continue;
                default:
                    if (!(t = _.trys, t = t.length > 0 && t[t.length - 1]) && (op[0] === 6 || op[0] === 2)) { _ = 0; continue; }
                    if (op[0] === 3 && (!t || (op[1] > t[0] && op[1] < t[3]))) { _.label = op[1]; break; }
                    if (op[0] === 6 && _.label < t[1]) { _.label = t[1]; t = op; break; }
                    if (t && _.label < t[2]) { _.label = t[2]; _.ops.push(op); break; }
                    if (t[2]) _.ops.pop();
                    _.trys.pop(); continue;
            }
            op = body.call(thisArg, _);
        } catch (e) { op = [6, e]; y = 0; } finally { f = t = 0; }
        if (op[0] & 5) throw op[1]; return { value: op[0] ? op[1] : void 0, done: true };
    }
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.OSNotificationPermission = void 0;
var NotificationReceivedEvent_1 = require("./NotificationReceivedEvent");
var OSNotificationPermission;
(function (OSNotificationPermission) {
    OSNotificationPermission[OSNotificationPermission["NotDetermined"] = 0] = "NotDetermined";
    OSNotificationPermission[OSNotificationPermission["Denied"] = 1] = "Denied";
    OSNotificationPermission[OSNotificationPermission["Authorized"] = 2] = "Authorized";
    OSNotificationPermission[OSNotificationPermission["Provisional"] = 3] = "Provisional";
    OSNotificationPermission[OSNotificationPermission["Ephemeral"] = 4] = "Ephemeral";
})(OSNotificationPermission = exports.OSNotificationPermission || (exports.OSNotificationPermission = {}));
var Notifications = /** @class */ (function () {
    function Notifications() {
        this._permissionObserverList = [];
        this._notificationClickedListeners = [];
        this._notificationWillDisplayListeners = [];
    }
    Notifications.prototype._processFunctionList = function (array, param) {
        for (var i = 0; i < array.length; i++) {
            array[i](param);
        }
    };
    /**
     * Sets initial permission value and adds observer for changes.
     * This internal method is kept to support the deprecated method {@link hasPermission}.
     */
    Notifications.prototype._setPropertyAndObserver = function () {
        var _this = this;
        var getPermissionCallback = function (granted) {
            _this._permission = granted;
        };
        window.cordova.exec(getPermissionCallback, function () { }, "OneSignalPush", "getPermissionInternal");
        this.addEventListener("permissionChange", function (result) {
            _this._permission = result;
        });
    };
    /**
     * @deprecated This method is deprecated. It has been replaced by {@link getPermissionAsync}.
     */
    Notifications.prototype.hasPermission = function () {
        return this._permission || false;
    };
    /**
     * Whether this app has push notification permission. Returns true if the user has accepted permissions,
     * or if the app has ephemeral or provisional permission.
     */
    Notifications.prototype.getPermissionAsync = function () {
        return __awaiter(this, void 0, void 0, function () {
            return __generator(this, function (_a) {
                return [2 /*return*/, new Promise(function (resolve, reject) {
                        window.cordova.exec(resolve, reject, "OneSignalPush", "getPermissionInternal");
                    })];
            });
        });
    };
    /** iOS Only.
     * Returns the enum for the native permission of the device. It will be one of:
     * OSNotificationPermissionNotDetermined,
     * OSNotificationPermissionDenied,
     * OSNotificationPermissionAuthorized,
     * OSNotificationPermissionProvisional - only available in iOS 12,
     * OSNotificationPermissionEphemeral - only available in iOS 14
     *
     * @returns {Promise<OSNotificationPermission>}
     *
     * */
    Notifications.prototype.permissionNative = function () {
        return new Promise(function (resolve, reject) {
            window.cordova.exec(resolve, reject, "OneSignalPush", "permissionNative", []);
        });
    };
    /**
     * Prompt the user for permission to receive push notifications. This will display the native system prompt to request push notification permission.
     * Use the fallbackToSettings parameter to prompt to open the settings app if a user has already declined push permissions.
     *
     *
     * @param  {boolean} fallbackToSettings
     * @returns {Promise<boolean>}
     */
    Notifications.prototype.requestPermission = function (fallbackToSettings) {
        var fallback = fallbackToSettings !== null && fallbackToSettings !== void 0 ? fallbackToSettings : false;
        return new Promise(function (resolve, reject) {
            window.cordova.exec(resolve, reject, "OneSignalPush", "requestPermission", [fallback]);
        });
    };
    ;
    /**
     * Whether attempting to request notification permission will show a prompt. Returns true if the device has not been prompted for push notification permission already.
     * @returns {Promise<boolean>}
     */
    Notifications.prototype.canRequestPermission = function () {
        return new Promise(function (resolve, reject) {
            window.cordova.exec(resolve, reject, "OneSignalPush", "canRequestPermission", []);
        });
    };
    ;
    /**
     * iOS Only
     */
    /**
     * Instead of having to prompt the user for permission to send them push notifications, your app can request provisional authorization.
     *
     * For more information: https://documentation.onesignal.com/docs/ios-customizations#provisional-push-notifications
     *
     * @param  {(response: boolean)=>void} handler
     * @returns void
     */
    Notifications.prototype.registerForProvisionalAuthorization = function (handler) {
        window.cordova.exec(handler, function () { }, "OneSignalPush", "registerForProvisionalAuthorization", []);
    };
    ;
    /**
     * Add listeners for notification events.
     * @param event
     * @param listener
     * @returns
     */
    Notifications.prototype.addEventListener = function (event, listener) {
        var _this = this;
        if (event === "click") {
            this._notificationClickedListeners.push(listener);
            var clickParsingHandler = function (json) {
                _this._processFunctionList(_this._notificationClickedListeners, json);
            };
            window.cordova.exec(clickParsingHandler, function () { }, "OneSignalPush", "addNotificationClickListener", []);
        }
        else if (event === "foregroundWillDisplay") {
            this._notificationWillDisplayListeners.push(listener);
            var foregroundParsingHandler = function (notification) {
                _this._notificationWillDisplayListeners.forEach(function (listener) {
                    listener(new NotificationReceivedEvent_1.NotificationWillDisplayEvent(notification));
                });
                window.cordova.exec(function () { }, function () { }, "OneSignalPush", "proceedWithWillDisplay", [notification.notificationId]);
            };
            window.cordova.exec(foregroundParsingHandler, function () { }, "OneSignalPush", "addForegroundLifecycleListener", []);
        }
        else if (event === "permissionChange") {
            this._permissionObserverList.push(listener);
            var permissionCallBackProcessor = function (state) {
                _this._processFunctionList(_this._permissionObserverList, state);
            };
            window.cordova.exec(permissionCallBackProcessor, function () { }, "OneSignalPush", "addPermissionObserver", []);
        }
        else {
            return;
        }
    };
    /**
     * Remove listeners for notification events.
     * @param event
     * @param listener
     * @returns
     */
    Notifications.prototype.removeEventListener = function (event, listener) {
        if (event === "click") {
            var index = this._notificationClickedListeners.indexOf(listener);
            if (index !== -1) {
                this._notificationClickedListeners.splice(index, 1);
            }
        }
        else if (event === "foregroundWillDisplay") {
            var index = this._notificationWillDisplayListeners.indexOf(listener);
            if (index !== -1) {
                this._notificationWillDisplayListeners.splice(index, 1);
            }
        }
        else if (event === "permissionChange") {
            var index = this._permissionObserverList.indexOf(listener);
            if (index !== -1) {
                this._permissionObserverList.splice(index, 1);
            }
        }
        else {
            return;
        }
    };
    /**
     * Removes all OneSignal notifications.
     * @returns void
     */
    Notifications.prototype.clearAll = function () {
        window.cordova.exec(function () { }, function () { }, "OneSignalPush", "clearAllNotifications", []);
    };
    ;
    /**
     * Android Only
     */
    /**
     * Android only.
     * Cancels a single OneSignal notification based on its Android notification integer ID. Use instead of Android's [android.app.NotificationManager.cancel], otherwise the notification will be restored when your app is restarted.
     * @param  {number} id - notification id to cancel
     * @returns void
     */
    Notifications.prototype.removeNotification = function (id) {
        window.cordova.exec(function () { }, function () { }, "OneSignalPush", "removeNotification", [id]);
    };
    ;
    /**
     * Android only.
     * Cancels a group of OneSignal notifications with the provided group key. Grouping notifications is a OneSignal concept, there is no [android.app.NotificationManager] equivalent.
     * @param  {string} id - notification group id to cancel
     * @returns void
     */
    Notifications.prototype.removeGroupedNotifications = function (id) {
        window.cordova.exec(function () { }, function () { }, "OneSignalPush", "removeGroupedNotifications", [id]);
    };
    ;
    return Notifications;
}());
exports.default = Notifications;

});
