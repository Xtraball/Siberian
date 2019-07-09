var genericMessage = "This usage was removed in 4.17, please use %s instead, this warning message and fallback will be removed in 4.18";
var noSuchFallback = {
    __noSuchMethod__: function(id, args) {}
};
// $cordovaCamera
angular.module("starter").factory("$cordovaCamera", [function () {
    console.error(genericMessage.replace("%s", "the `Picture` service"));
    return noSuchFallback;
}]);
// $cordovaActionSheet
angular.module("starter").factory("cordovaActionSheet", [function () {
    console.error(genericMessage.replace("%s", "the `$ionicActionSheet` service"));
    return noSuchFallback;
}]);
// $cordovaBarcodeScanner
angular.module("starter").factory("$cordovaBarcodeScanner", [function () {
    console.error(genericMessage.replace("%s", "the `$window.cordova.plugins.barcodeScanner` service"));
    return noSuchFallback;
}]);
// $cordovaClipboard
angular.module("starter").factory("$cordovaClipboard", [function () {
    console.error(genericMessage.replace("%s", "the `$window.cordova.plugins.clipboard` service"));
    return noSuchFallback;
}]);
// $cordovaGeolocation
angular.module("starter").factory("$cordovaGeolocation", [function () {
    console.error(genericMessage.replace("%s", "the `Location` service"));
    return noSuchFallback;
}]);
// $cordovaLocalNotification
angular.module("starter").factory("$cordovaLocalNotification", [function () {
    console.error(genericMessage.replace("%s", "the `$window.cordova.plugins.notification.local` service"));
    return noSuchFallback;
}]);
// $cordovaSocialSharing
angular.module("starter").factory("$cordovaSocialSharing", [function () {
    console.error(genericMessage.replace("%s", "the `SocialSharing` service"));
    return noSuchFallback;
}]);
// $cordovaDevice
angular.module("starter").factory("$cordovaDevice", [function () {
    console.error(genericMessage.replace("%s", "the `device` constant"));
    return noSuchFallback;
}]);