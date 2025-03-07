cordova.define("Onesignal.PushSubscriptionNamespace", function(require, exports, module) {
"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
var PushSubscription = /** @class */ (function () {
    function PushSubscription() {
        this._subscriptionObserverList = [];
    }
    PushSubscription.prototype._processFunctionList = function (array, param) {
        for (var i = 0; i < array.length; i++) {
            array[i](param);
        }
    };
    /**
     * Sets initial Push Subscription properties and adds observer for changes.
     * This internal method is kept to support the deprecated methods {@link id}, {@link token}, {@link optedIn}.
     */
    PushSubscription.prototype._setPropertiesAndObserver = function () {
        var _this = this;
        /**
         * Receive push Id
         * @param obj
         */
        var getIdCallback = function (id) {
            _this._id = id;
        };
        window.cordova.exec(getIdCallback, function () { }, "OneSignalPush", "getPushSubscriptionId");
        /**
         * Receive token
         * @param obj
         */
        var getTokenCallback = function (token) {
            _this._token = token;
        };
        window.cordova.exec(getTokenCallback, function () { }, "OneSignalPush", "getPushSubscriptionToken");
        /**
         * Receive opted-in status
         * @param granted
         */
        var getOptedInCallback = function (granted) {
            _this._optedIn = granted;
        };
        window.cordova.exec(getOptedInCallback, function () { }, "OneSignalPush", "getPushSubscriptionOptedIn");
        this.addEventListener("change", function (subscriptionChange) {
            _this._id = subscriptionChange.current.id;
            _this._token = subscriptionChange.current.token;
            _this._optedIn = subscriptionChange.current.optedIn;
        });
    };
    Object.defineProperty(PushSubscription.prototype, "id", {
        /**
         * @deprecated This method is deprecated. It has been replaced by {@link getIdAsync}.
         */
        get: function () {
            console.warn("OneSignal: This method has been deprecated. Use getIdAsync instead for getting push subscription id.");
            return this._id;
        },
        enumerable: false,
        configurable: true
    });
    Object.defineProperty(PushSubscription.prototype, "token", {
        /**
         * @deprecated This method is deprecated. It has been replaced by {@link getTokenAsync}.
         */
        get: function () {
            console.warn("OneSignal: This method has been deprecated. Use getTokenAsync instead for getting push subscription token.");
            return this._token;
        },
        enumerable: false,
        configurable: true
    });
    Object.defineProperty(PushSubscription.prototype, "optedIn", {
        /**
         * @deprecated This method is deprecated. It has been replaced by {@link getOptedInAsync}.
         */
        get: function () {
            console.warn("OneSignal: This method has been deprecated. Use getOptedInAsync instead for getting push subscription opted in status.");
            return this._optedIn || false;
        },
        enumerable: false,
        configurable: true
    });
    /**
     * The readonly push subscription ID.
     * @returns {Promise<string | null>}
     */
    PushSubscription.prototype.getIdAsync = function () {
        return new Promise(function (resolve, reject) {
            window.cordova.exec(resolve, reject, "OneSignalPush", "getPushSubscriptionId");
        });
    };
    /**
     * The readonly push token.
     * @returns {Promise<string | null>}
     */
    PushSubscription.prototype.getTokenAsync = function () {
        return new Promise(function (resolve, reject) {
            window.cordova.exec(resolve, reject, "OneSignalPush", "getPushSubscriptionToken");
        });
    };
    /**
     * Gets a boolean value indicating whether the current user is opted in to push notifications.
     * This returns true when the app has notifications permission and optOut() is NOT called.
     * Note: Does not take into account the existence of the subscription ID and push token.
     * This boolean may return true but push notifications may still not be received by the user.
     * @returns {Promise<boolean>}
     */
    PushSubscription.prototype.getOptedInAsync = function () {
        return new Promise(function (resolve, reject) {
            window.cordova.exec(resolve, reject, "OneSignalPush", "getPushSubscriptionOptedIn");
        });
    };
    /**
     * Add a callback that fires when the OneSignal push subscription state changes.
     * @param  {(event: PushSubscriptionChangedState)=>void} listener
     * @returns void
     */
    PushSubscription.prototype.addEventListener = function (event, listener) {
        var _this = this;
        this._subscriptionObserverList.push(listener);
        var subscriptionCallBackProcessor = function (state) {
            _this._processFunctionList(_this._subscriptionObserverList, state);
        };
        window.cordova.exec(subscriptionCallBackProcessor, function () { }, "OneSignalPush", "addPushSubscriptionObserver", []);
    };
    /**
     * Remove a push subscription observer that has been previously added.
     * @param  {(event: PushSubscriptionChangedState)=>void} listener
     * @returns void
     */
    PushSubscription.prototype.removeEventListener = function (event, listener) {
        var index = this._subscriptionObserverList.indexOf(listener);
        if (index !== -1) {
            this._subscriptionObserverList.splice(index, 1);
        }
    };
    /**
     * Call this method to receive push notifications on the device or to resume receiving of push notifications after calling optOut. If needed, this method will prompt the user for push notifications permission.
     * @returns void
     */
    PushSubscription.prototype.optIn = function () {
        window.cordova.exec(function () { }, function () { }, "OneSignalPush", "optInPushSubscription");
    };
    /**
     * If at any point you want the user to stop receiving push notifications on the current device (regardless of system-level permission status), you can call this method to opt out.
     * @returns void
     */
    PushSubscription.prototype.optOut = function () {
        window.cordova.exec(function () { }, function () { }, "OneSignalPush", "optOutPushSubscription");
    };
    return PushSubscription;
}());
exports.default = PushSubscription;

});
