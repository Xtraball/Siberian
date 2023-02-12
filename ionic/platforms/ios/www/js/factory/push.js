// DUMMY DEPRECATED
angular.module('starter').factory('Push', function ($pwaRequest, $session, SB) {
    var factory = {};
    factory.setValueId = function (valueId) {};
    factory.setExtendedOptions = function (options) {};
    factory.preFetch = function (page) {};
    factory.isRegistered = function (params) {return $pwaRequest.reject("DEPRECATED");};
    factory.registerAndroidDevice = function (params) {return $pwaRequest.reject("DEPRECATED");};
    factory.registerIosDevice = function (params) {return $pwaRequest.reject("DEPRECATED");};
    factory.deletePush = function (deliverId) {return $pwaRequest.reject("DEPRECATED");};
    factory.findAll = function (offset, refresh) {return $pwaRequest.reject("DEPRECATED");};
    factory.getSample = function () {return $pwaRequest.reject("DEPRECATED");};
    factory.updateUnreadCount = function () {return $pwaRequest.reject("DEPRECATED");};
    factory.getInAppMessages = function () {return $pwaRequest.reject("DEPRECATED");};
    factory.getLastMessages = function (cache) {return $pwaRequest.reject("DEPRECATED");};
    factory.markInAppAsRead = function () {return $pwaRequest.reject("DEPRECATED");};
    factory.markAsDisplayed = function (messageId) {return $pwaRequest.reject("DEPRECATED");};
    return factory;
});
