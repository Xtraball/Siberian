cordova.define("Onesignal.NotificationReceivedEvent", function(require, exports, module) {
"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.NotificationWillDisplayEvent = void 0;
var OSNotification_1 = require("./OSNotification");
var NotificationWillDisplayEvent = /** @class */ (function () {
    function NotificationWillDisplayEvent(displayEvent) {
        this.notification = new OSNotification_1.OSNotification(displayEvent);
    }
    NotificationWillDisplayEvent.prototype.preventDefault = function () {
        window.cordova.exec(function () { }, function () { }, "OneSignalPush", "preventDefault", [this.notification.notificationId]);
        return;
    };
    NotificationWillDisplayEvent.prototype.getNotification = function () {
        return this.notification;
    };
    return NotificationWillDisplayEvent;
}());
exports.NotificationWillDisplayEvent = NotificationWillDisplayEvent;

});
