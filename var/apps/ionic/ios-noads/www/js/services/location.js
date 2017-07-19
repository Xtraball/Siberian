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
angular.module("starter").service("Location", function($cordovaGeolocation, $q) {

    var service = {
        last_fetch: null,
        position: null
    };

    /**
     * Default timeout is 10 seconds
     *
     * @param config
     * @returns {*|promise}
     */
    service.getLocation = function(config, force) {

        var deferred = $q.defer();
        var is_resolved = false;

        force = (force !== undefined);

        config = angular.extend({
            enableHighAccuracy  : true,
            timeout             : 10000,
            maximumAge          : 0
        }, config);

        if(!force && (service.last_fetch !== null) && ((service.last_fetch + 420000) > Date.now())) {
            // fresh poll, send direct
            deferred.resolve(service.position);
            is_resolved = true;
        }

        $cordovaGeolocation.getCurrentPosition(config)
            .then(function(position) {

                service.last_fetch = Date.now();
                service.position = position;

                if(!is_resolved) {
                    deferred.resolve(service.position);
                }

            }, function() {

                if(!is_resolved) {
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
    service.getLatest = function() {
        var deferred = $q.defer();

        if(service.last_fetch === null) {

            /** Try to fetch it. */
            service.getLocation()
                .then(function(position) {
                    deferred.resolve(position);
                }, function() {
                    deferred.reject(false);
                });

        } else {
            deferred.resolve(service.position);
        }

        return deferred.promise;
    };

    return service;
});