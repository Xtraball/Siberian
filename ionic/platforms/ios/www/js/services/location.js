/*global
 App, angular, IS_NATIVE_APP
 */

/**
 * Location, location and coordinates should be acquired fast,
 * we are using timeouts and promise to send answer as fast as possible.
 *
 * @author Xtraball SAS
 *
 */
angular.module('starter').service('Location', function ($cordovaGeolocation, $q) {
    var service = {
        lastFetch: null,
        position: null
    };

    /**
     * Default timeout is 10 seconds
     *
     * @param config
     * @param force
     * @returns {*|promise}
     */
    service.getLocation = function (config, force) {
        var deferred = $q.defer();
        var isResolved = false;

        var localForce = (force !== undefined);

        var localConfig = angular.extend({
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
        }, config);

        if (!localForce && (service.lastFetch !== null) && ((service.lastFetch + 42000) > Date.now())) {
            console.log("send immediate value, then re-poll in background: ", Date.now(), service.position);
            // fresh poll, send direct
            deferred.resolve(service.position);
            isResolved = true;
        }

        $cordovaGeolocation.getCurrentPosition(localConfig)
            .then(function (position) {
                console.log("repoll location service: ", Date.now(), position);
                service.lastFetch = Date.now();
                service.position = position;
                if (!isResolved) {
                    deferred.resolve(service.position);
                }
            }, function () {
                console.log("re-poll location service: ", Date.now(), "error");
                if (!isResolved) {
                    deferred.reject();
                }
            });

        return deferred.promise;
    };

    /**
     * Returns the latest fetch position, if there is one, or false
     *
     * @returns {null}
     */
    service.getLatest = function () {
        var deferred = $q.defer();

        if (service.lastFetch === null) {
            // Try to fetch it!
            service.getLocation()
                .then(function (position) {
                    deferred.resolve(position);
                }, function () {
                    deferred.reject(false);
                });
        } else {
            deferred.resolve(service.position);
        }

        return deferred.promise;
    };

    return service;
});
