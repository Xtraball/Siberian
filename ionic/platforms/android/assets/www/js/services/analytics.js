/**
 * Analytics request handler!
 */
angular.module('starter').service('Analytics', function ($pwaRequest, $q, $rootScope, $log) {
    var service = {};

    service.data = {};

    service.storeInstallation = function () {
        var params = {
            OS: device.platform,
            OSVersion: device.version,
            Device: device.platform,
            DeviceVersion: device.model,
            deviceUUID: device.uuid,
            latitude: null,
            longitude: null
        };

        service.postData('analytics/mobile_store/installation', params);
    };

    service.storeOpening = function () {
        var deferred = $q.defer();
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

        service.postData('analytics/mobile_store/opening', params)
            .then(function (result) {
                deferred.resolve(result);
            }).catch(function (error) {
                deferred.reject();
            });

        return deferred.promise;
    };

    service.storeClosing = function () {
        if (typeof service.data.storeClosingId === 'undefined') {
            $log.debug('aborting /analytics/mobile_store/closing, no id.');
            return;
        }

        var params = {
            id: service.data.storeClosingId
        };

        service.postData('analytics/mobile_store/closing', params);
    };

    service.storePageOpening = function (page) {
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

        service.postData('analytics/mobile_store/pageopening', params);
    };

    service.storeProductOpening = function (product) {
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

        service.postData('analytics/mobile_store/productopening', params);
    };

    service.storeProductSold = function (products) {
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

        service.postData('analytics/mobile_store/productsold', params);
    };

    service.postData = function (url, params) {
        if (!isOverview) {
            return $pwaRequest.post(url, {
                data: params,
                cache: false,
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            });
        }
        return $pwaRequest.reject('Analytics are disabled in Overview!');
    };

    return service;
});
