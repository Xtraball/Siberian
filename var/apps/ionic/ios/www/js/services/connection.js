/*global
    angular, DOMAIN
 */
angular.module("starter").service("Connection", function ($ionicPlatform, $rootScope,
                                                          $translate, $window, $log, $http, Dialog) {

    var service = {};

    var _isOnline = true;

    Object.defineProperty(service, "isOnline", {
        get: function () {
            return _isOnline;
        }
    });

    Object.defineProperty(service, "isOffline", {
        get: function () {
            return !service.isOnline;
        }
    });

    service.show_popup = null;

    var callbackFromNative = function (data) {
        if (service.isOnline === data.isOnline) {
            return;
        }

        _isOnline = data.isOnline;

        if (!service.show_popup && $rootScope.isNativeApp && !_isOnline) {
            service.show_popup = true;

            if(!$rootScope.onPause) {
                Dialog.alert($translate.instant("Info"),
                    $translate.instant("You have gone offline"),
                    $translate.instant("OK"), -1)
                    .then(function() {
                        service.show_popup = null;
                    });
            }

        }

        $rootScope.$broadcast("connectionStateChange", data);

        if (_isOnline) {
            $log.info("App is now online.");
            $window.StatusBar.backgroundColorByHexString("#000000");
        } else {
            $log.info("App is offline.");
            $window.StatusBar.backgroundColorByHexString("#d54c16");
        }

    };

    $ionicPlatform.ready(function () {
        if ($rootScope.isNativeApp && $window.OfflineMode) {
            $window.OfflineMode.setCheckConnectionURL(DOMAIN + "/check_connection.php");
            $window.OfflineMode.registerCallback(callbackFromNative);
        }
    });

    return service;
});
