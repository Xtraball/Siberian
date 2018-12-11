/**
 * Analytics request handler!
 */
angular.module('starter').service('Analytics', function ($pwaRequest, $session, $q, $rootScope, $log) {
    var service = {};

    service.data = {};

    /**
     *
     * @type {Array}
     */
    service.pool = [];

    service.storeInstallation = function () {
        var params = {
            date: Date.now(),
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
            date: Date.now(),
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
            date: Date.now(),
            id: service.data.storeClosingId
        };

        service.postData('analytics/mobile_store/closing', params);
    };

    service.storePageOpening = function (page) {
        var params = {
            date: Date.now(),
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
            date: Date.now(),
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
            date: Date.now(),
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
            var request = {
                url: url,
                params: params
            };

            if (service.pool.length < 10) {
                service.pool.push(request);

                $session.setItem("analytics_pool", service.pool);
            } else {
                var pool = $session.getItem("analytics_pool").then(function(pool) {
                    if (pool) {
                        service.pool = pool;
                    }

                    service.pool.push(request);
                    service.commitPool();
                });
            }
        }

        return $pwaRequest.reject('Analytics are disabled in Overview!');
    };

    service.commitPool = function () {
        service.pool.forEach(function(value) {
            return $pwaRequest.post(value.url, {
                data: value.params,
                cache: false,
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            });
        });

        // Empty the pool!
        $session.setItem("analytics_pool", []);
    };

    // Onload check for old pool not committed
    $session
        .getItem("analytics_pool")
        .then(function(pool) {
            if (pool) {
                service.pool = pool;
            }

            service.pool.push(request);
            service.commitPool();
        });

    return service;
});
