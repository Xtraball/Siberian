cordova.define("Onesignal.Subscription", function(require, exports, module) {
"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.DeviceState = void 0;
/* D E V I C E */
var DeviceState = /** @class */ (function () {
    // areNotificationsEnabled (android) not included since it is converted to hasNotificationPermission below
    function DeviceState(deviceState) {
        this.userId = deviceState.userId;
        this.pushToken = deviceState.pushToken;
        this.emailUserId = deviceState.emailUserId;
        this.emailAddress = deviceState.emailAddress;
        this.smsUserId = deviceState.smsUserId;
        this.smsNumber = deviceState.smsNumber;
        // rename the subscribed properties to align with existing type definition
        this.pushDisabled = deviceState.isPushDisabled;
        this.subscribed = deviceState.isSubscribed;
        this.emailSubscribed = deviceState.isEmailSubscribed;
        this.smsSubscribed = deviceState.isSMSSubscribed;
        if (deviceState.areNotificationsEnabled !== undefined) {
            this.hasNotificationPermission = deviceState.areNotificationsEnabled;
        }
        else {
            this.hasNotificationPermission = deviceState.hasNotificationPermission;
        }
    }
    return DeviceState;
}());
exports.DeviceState = DeviceState;

});
