cordova.define("Onesignal.InAppMessagesNamespace", function(require, exports, module) {
"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
var InAppMessages = /** @class */ (function () {
    function InAppMessages() {
        this._inAppMessageClickListeners = [];
        this._willDisplayInAppMessageListeners = [];
        this._didDisplayInAppMessageListeners = [];
        this._willDismissInAppMessageListeners = [];
        this._didDismissInAppMessageListeners = [];
    }
    InAppMessages.prototype._processFunctionList = function (array, param) {
        for (var i = 0; i < array.length; i++) {
            array[i](param);
        }
    };
    /**
     * Add event listeners for In-App Message click and/or lifecycle events.
     * @param event
     * @param listener
     * @returns
     */
    InAppMessages.prototype.addEventListener = function (event, listener) {
        var _this = this;
        if (event === "click") {
            this._inAppMessageClickListeners.push(listener);
            var inAppMessageClickListener = function (json) {
                _this._processFunctionList(_this._inAppMessageClickListeners, json);
            };
            window.cordova.exec(inAppMessageClickListener, function () { }, "OneSignalPush", "setInAppMessageClickHandler", []);
        }
        else if (event === "willDisplay") {
            this._willDisplayInAppMessageListeners.push(listener);
            var willDisplayCallBackProcessor = function (event) {
                _this._processFunctionList(_this._willDisplayInAppMessageListeners, event);
            };
            window.cordova.exec(willDisplayCallBackProcessor, function () { }, "OneSignalPush", "setOnWillDisplayInAppMessageHandler", []);
        }
        else if (event === "didDisplay") {
            this._didDisplayInAppMessageListeners.push(listener);
            var didDisplayCallBackProcessor = function (event) {
                _this._processFunctionList(_this._didDisplayInAppMessageListeners, event);
            };
            window.cordova.exec(didDisplayCallBackProcessor, function () { }, "OneSignalPush", "setOnDidDisplayInAppMessageHandler", []);
        }
        else if (event === "willDismiss") {
            this._willDismissInAppMessageListeners.push(listener);
            var willDismissInAppMessageProcessor = function (event) {
                _this._processFunctionList(_this._willDismissInAppMessageListeners, event);
            };
            window.cordova.exec(willDismissInAppMessageProcessor, function () { }, "OneSignalPush", "setOnWillDismissInAppMessageHandler", []);
        }
        else if (event === "didDismiss") {
            this._didDismissInAppMessageListeners.push(listener);
            var didDismissInAppMessageCallBackProcessor = function (event) {
                _this._processFunctionList(_this._didDismissInAppMessageListeners, event);
            };
            window.cordova.exec(didDismissInAppMessageCallBackProcessor, function () { }, "OneSignalPush", "setOnDidDismissInAppMessageHandler", []);
        }
        else {
            return;
        }
    };
    /**
     * Remove event listeners for In-App Message click and/or lifecycle events.
     * @param event
     * @param listener
     * @returns
     */
    InAppMessages.prototype.removeEventListener = function (event, listener) {
        if (event === "click") {
            var index = this._inAppMessageClickListeners.indexOf(listener);
            if (index !== -1) {
                this._inAppMessageClickListeners.splice(index, 1);
            }
        }
        else {
            if (event === "willDisplay") {
                var index = this._willDisplayInAppMessageListeners.indexOf(listener);
                if (index !== -1) {
                    this._willDisplayInAppMessageListeners.splice(index, 1);
                }
            }
            else if (event === "didDisplay") {
                var index = this._didDisplayInAppMessageListeners.indexOf(listener);
                if (index !== -1) {
                    this._willDisplayInAppMessageListeners.splice(index, 1);
                }
            }
            else if (event === "willDismiss") {
                var index = this._willDismissInAppMessageListeners.indexOf(listener);
                if (index !== -1) {
                    this._willDismissInAppMessageListeners.splice(index, 1);
                }
            }
            else if (event === "didDismiss") {
                var index = this._didDismissInAppMessageListeners.indexOf(listener);
                if (index !== -1) {
                    this._didDismissInAppMessageListeners.splice(index, 1);
                }
            }
            else {
                return;
            }
        }
    };
    /**
     * Add a trigger for the current user. Triggers are currently explicitly used to determine whether a specific IAM should be displayed to the user.
     * @param  {string} key
     * @param  {string} value
     * @returns void
     */
    InAppMessages.prototype.addTrigger = function (key, value) {
        var _a;
        var obj = (_a = {}, _a[key] = value, _a);
        this.addTriggers(obj);
    };
    ;
    /**
     * Add multiple triggers for the current user. Triggers are currently explicitly used to determine whether a specific IAM should be displayed to the user.
     * @param  {[key: string]: string} triggers
     * @returns void
     */
    InAppMessages.prototype.addTriggers = function (triggers) {
        Object.keys(triggers).forEach(function (key) {
            // forces values to be string types
            if (typeof triggers[key] !== "string") {
                triggers[key] = JSON.stringify(triggers[key]);
            }
        });
        window.cordova.exec(function () { }, function () { }, "OneSignalPush", "addTriggers", [triggers]);
    };
    ;
    /**
     * Remove the trigger with the provided key from the current user.
     * @param  {string} key
     * @returns void
     */
    InAppMessages.prototype.removeTrigger = function (key) {
        this.removeTriggers([key]);
    };
    ;
    /**
     * Remove multiple triggers from the current user.
     * @param  {string[]} keys
     * @returns void
     */
    InAppMessages.prototype.removeTriggers = function (keys) {
        if (!Array.isArray(keys)) {
            console.error("OneSignal: removeTriggers: argument must be of type Array");
        }
        window.cordova.exec(function () { }, function () { }, "OneSignalPush", "removeTriggers", [keys]);
    };
    ;
    /**
     * Clear all triggers from the current user.
     * @returns void
     */
    InAppMessages.prototype.clearTriggers = function () {
        window.cordova.exec(function () { }, function () { }, "OneSignalPush", "clearTriggers");
    };
    /**
     * Set whether in-app messaging is currently paused.
     * When set to true no IAM will be presented to the user regardless of whether they qualify for them.
     * When set to 'false` any IAMs the user qualifies for will be presented to the user at the appropriate time.
     * @param  {boolean} pause
     * @returns void
     */
    InAppMessages.prototype.setPaused = function (pause) {
        window.cordova.exec(function () { }, function () { }, "OneSignalPush", "setPaused", [pause]);
    };
    ;
    /**
     * Whether in-app messaging is currently paused.
     * @returns {Promise<boolean>}
     */
    InAppMessages.prototype.getPaused = function () {
        return new Promise(function (resolve, reject) {
            window.cordova.exec(resolve, reject, "OneSignalPush", "isPaused", []);
        });
    };
    ;
    return InAppMessages;
}());
exports.default = InAppMessages;

});
