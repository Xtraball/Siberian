/* global
    App, device, cordova
 */
angular.module('starter').service('Analytics', function ($cordovaGeolocation, $pwaRequest, $q, $log, $rootScope,
                                                         Application, Url) {
    var service = {};

    service.data = {};

    service.storeInstallation = function () {
        if (!Application.is_webview) {
            var url = Url.get('analytics/mobile_store/installation');
            var params = {
                OS: device.platform,
                OSVersion: device.version,
                Device: device.platform,
                DeviceVersion: device.model,
                deviceUUID: device.uuid,
                latitude: null,
                longitude: null
            };

            $cordovaGeolocation.getCurrentPosition({
                enableHighAccuracy: true,
                timeout: 30000,
                maximumAge: 0
            }).then(function (position) {
                params.latitude = position.coords.latitude;
                params.longitude = position.coords.longitude;

                service.postData(url, params);
            }, function () {
                service.postData(url, params);
            });
        }
    };

    service.storeOpening = function () {
        var deferred = $q.defer();

        if (!Application.is_webview && (typeof cordova !== 'undefined')) {
            var url = Url.get('analytics/mobile_store/opening');
            var params = {
                OS: cordova.device ? device.platform : 'Browser',
                OSVersion: cordova.device ? device.version : null,
                Device: cordova.device ? device.platform : 'Browser',
                DeviceVersion: cordova.device ? device.model : null,
                deviceUUID: cordova.device ? device.uuid : null,
                latitude: null,
                longitude: null,
                locale: CURRENT_LANGUAGE
            };

            $cordovaGeolocation.getCurrentPosition({
                enableHighAccuracy: true,
                timeout: 30000,
                maximumAge: 0
            }).then(function (position) {
                params.latitude = position.coords.latitude;
                params.longitude = position.coords.longitude;

                service.postData(url, params).then(function (result) {
                    deferred.resolve(result);
                }).catch(function (error) {

                });
            }, function () {
                service.postData(url, params).then(function (result) {
                    deferred.resolve(result);
                }).catch(function (error) {

                });
            });
        }

        return deferred.promise;
    };

    service.storeClosing = function () {
        if (!$rootScope.isOverview) {
            var url = Url.get('analytics/mobile_store/closing');

            if (typeof service.data.storeClosingId === 'undefined') {
                $log.debug('aborting /analytics/mobile_store/closing, no id.');
                return;
            }

            var params = {
                id: service.data.storeClosingId
            };

            service.postData(url, params);
        }
    };

    service.storePageOpening = function (page) {
        if (!$rootScope.isOverview) {
            var url = Url.get('analytics/mobile_store/pageopening');
            var params = {
                featureId: page.value_id,
                OS: cordova.device ? device.platform : 'Browser',
                OSVersion: cordova.device ? device.version : null,
                Device: cordova.device ? device.platform : 'Browser',
                DeviceVersion: cordova.device ? device.model : null,
                deviceUUID: cordova.device ? device.uuid : null,
                latitude: null,
                longitude: null,
                locale: CURRENT_LANGUAGE
            };

            $cordovaGeolocation.getCurrentPosition({
                enableHighAccuracy: true,
                timeout: 30000,
                maximumAge: 0
            }).then(function (position) {
                params.latitude = position.coords.latitude;
                params.longitude = position.coords.longitude;

                service.postData(url, params);
            }, function () {
                service.postData(url, params);
            });
        }
    };

    service.storeProductOpening = function (product) {
        if (!$rootScope.isOverview) {
            var url = Url.get('analytics/mobile_store/productopening');
            var params = {
                productId: product.id,
                name: product.name,
                OS: cordova.device ? device.platform : 'Browser',
                OSVersion: cordova.device ? device.version : null,
                Device: cordova.device ? device.platform : 'Browser',
                DeviceVersion: cordova.device ? device.model : null,
                deviceUUID: cordova.device ? device.uuid : null,
                latitude: null,
                longitude: null,
                locale: CURRENT_LANGUAGE
            };

            $cordovaGeolocation.getCurrentPosition({
                enableHighAccuracy: true,
                timeout: 30000,
                maximumAge: 0
            }).then(function (position) {
                params.latitude = position.coords.latitude;
                params.longitude = position.coords.longitude;

                service.postData(url, params);
            }, function () {
                service.postData(url, params);
            });
        }
    };

    service.storeProductSold = function (products) {
        if (!$rootScope.isOverview) {
            var url = Url.get('analytics/mobile_store/productsold');
            var params = {
                products: products,
                OS: cordova.device ? device.platform : 'Browser',
                OSVersion: cordova.device ? device.version : null,
                Device: cordova.device ? device.platform : 'Browser',
                DeviceVersion: cordova.device ? device.model : null,
                deviceUUID: cordova.device ? device.uuid : null,
                latitude: null,
                longitude: null,
                locale: CURRENT_LANGUAGE
            };

            $cordovaGeolocation.getCurrentPosition({
                enableHighAccuracy: true,
                timeout: 30000,
                maximumAge: 0
            }).then(function (position) {
                params.latitude = position.coords.latitude;
                params.longitude = position.coords.longitude;

                service.postData(url, params);
            }, function () {
                service.postData(url, params);
            });
        }
    };

    service.postData = function (url, params) {
        return $pwaRequest.post(url, {
            data: params,
            cache: false,
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            }
        });
    };

    return service;
});
