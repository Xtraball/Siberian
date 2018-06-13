angular.module('starter').service('ConnectionService', function ($ionicPlatform, $rootScope,
                                                                 $translate, $window, $log, $http, Dialog) {
    var service = {};

    var _isOnline = true;

    Object.defineProperty(service, 'isOnline', {
        get: function () {
            return _isOnline;
        }
    });

    Object.defineProperty(service, 'isOffline', {
        get: function () {
            return !service.isOnline;
        }
    });

    service.show_popup = null;

    service.callbackFromNative = function (data) {
        if (service.isOnline === data.isOnline) {
            return;
        }

        _isOnline = data.isOnline;

        if (!service.show_popup && $rootScope.isNativeApp && !_isOnline) {
            service.show_popup = true;

            if (!$rootScope.onPause) {
                Dialog
                .alert('Info', 'You have gone offline', 'OK', -1)
                .then(function () {
                    service.show_popup = null;
                });
            }
        }

        $rootScope.$broadcast('connectionStateChange', data);

        if (_isOnline) {
            $log.info('App is now online.');
        } else {
            $log.info('App is offline.');
        }
    };

    $ionicPlatform.ready(function () {
        if ($rootScope.isNativeApp && $window.OfflineMode && !IS_PREVIEW) {
            $window.OfflineMode.setCheckConnectionURL(DOMAIN + '/ping.txt');
            $window.OfflineMode.registerCallback(service.callbackFromNative);
        }
    });

    return service;
});
