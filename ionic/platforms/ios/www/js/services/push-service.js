// DUMMY DEPRECATED
angular.module('starter').service('PushService', function ($q) {
    var service = {push: null, isReady: null, isReadyPromise: null, isEnabled: true, settings: {android: {senderID: '01234567890', icon: 'ic_icon', iconColor: '#0099C7', sound: true, soundname: 'sb_beep4', vibrate: true}, ios: {clearBadge: false, critical: false, alert: true, badge: true, sound: true, soundname: 'sb_beep4',}, windows: {}}};
    service.configure = function (senderID, iconColor) {};
    service.init = function () {};
    service.isRegistered = function () {};
    service.register = function (registerOnly) {};
    service.registerDevice = function () {return $q.reject('DEPRECATED');};
    service.registerAndroid = function () {return $q.reject('DEPRECATED');};
    service.registerIos = function () {return $q.reject('DEPRECATED');};
    service.onNotificationReceived = function () {};
    service.updateUnreadCount = function () {};
    service.sendLocalNotification = function (messageId, title, message) {return $q.reject('DEPRECATED');};
    service.fetchMessagesOnStart = function () {};
    service.displayNotification = function (messagePayload) {};
    return service;
});
