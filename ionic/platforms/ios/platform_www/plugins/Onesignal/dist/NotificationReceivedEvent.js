cordova.define("Onesignal.NotificationReceivedEvent", function(require, exports, module) {
"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
var OSNotification_1 = __importDefault(require("./OSNotification"));
var NotificationReceivedEvent = /** @class */ (function () {
    function NotificationReceivedEvent(receivedEvent) {
        this.notification = new OSNotification_1.default(receivedEvent);
    }
    NotificationReceivedEvent.prototype.complete = function (notification) {
        if (!notification) {
            // if the notificationReceivedEvent is null, we want to call the native-side
            // complete/completion with null to silence the notification
            cordova.exec(function () { }, function () { }, "OneSignalPush", "completeNotification", [this.notification.notificationId, false]);
            return;
        }
        // if the notificationReceivedEvent is not null, we want to pass the specific event
        // future: Android side: make the notification modifiable
        // iOS & Android: the notification id is associated with the native-side complete handler / completion block
        cordova.exec(function () { }, function () { }, "OneSignalPush", "completeNotification", [this.notification.notificationId, true]);
    };
    NotificationReceivedEvent.prototype.getNotification = function () {
        return this.notification;
    };
    return NotificationReceivedEvent;
}());
exports.default = NotificationReceivedEvent;

});
