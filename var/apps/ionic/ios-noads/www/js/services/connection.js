App.service("Connection", function ($ionicPlatform, $rootScope, $translate, $window, $log, Dialog) {

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
            $window.StatusBar.backgroundColorByHexString("#000000");
            return;
        }

        _isOnline = data.isOnline;

        if (!service.show_popup && !$rootScope.is_webview && !_isOnline) {
            service.show_popup = true;

            Dialog.alert($translate.instant("Info"), $translate.instant("You have gone offline"), $translate.instant("OK")).then(function() {
                service.show_popup = null;
                $window.StatusBar.backgroundColorByHexString("#d54c16");
            });
        } else if (!$rootScope.is_webview && _isOnline) {
            $window.StatusBar.backgroundColorByHexString("#000000");
        }

        $rootScope.$broadcast("connectionStateChange", data);

        if (_isOnline) {
            $log.info("App is now online.");
        } else {
            $log.info("App is offline.");
        }

    };

    $ionicPlatform.ready(function () {
        if (!$rootScope.is_webview && $window.OfflineMode) {
            $window.OfflineMode.setCheckConnectionURL(DOMAIN + "/check_connection.php");
            $window.OfflineMode.registerCallback(callbackFromNative);
        }
    });

    return service;
});
