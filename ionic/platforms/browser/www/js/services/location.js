/**
 * Location, location and coordinates should be acquired fast,
 * we are using timeouts and promise to send answer as fast as possible.
 *
 * @author Xtraball SAS
 *
 */
angular.module('starter').service('Location', function ($rootScope, $cordovaGeolocation, $q, Dialog, Loader) {
    var service = {
        PERMISSION_DENIED: 1,
        POSITION_UNAVAILABLE: 2,
        TIMEOUT: 3,
        debug: false,
        lastFetch: null,
        position: null,
        isEnabled: true,
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

        var localRequestLocation = function (deferred) {
            if (!localForce && (service.lastFetch !== null) && ((service.lastFetch + 42000) > Date.now())) {
                // fresh poll, send direct
                deferred.resolve(service.position);
                isResolved = true;
            }

            $cordovaGeolocation
            .getCurrentPosition(localConfig)
            .then(function (position) {
                service.lastFetch = Date.now();
                service.position = position;
                if (service.debug) {
                    console.log("position ok", position);
                }
                if (!isResolved) {
                    deferred.resolve(service.position);
                }
            }, function (error) {
                if (service.debug) {
                    console.log("position ko");
                }
                if (error.code === service.TIMEOUT ||
                    error.code === service.PERMISSION_DENIED ||
                    error.code === service.POSITION_UNAVAILABLE) {
                    localReject(deferred);
                }
                if (!isResolved) {
                    deferred.reject();
                }
            });
        };

        var localReject = function (deferred) {
            // Disable for all next requests!
            service.isEnabled = false;
            deferred.reject();
        };

        if (service.isEnabled === false) {
            if (service.debug) {
                console.log("service disabled");
            }
            deferred.reject();
        } else {
            if (cordova.plugins.permissions !== undefined) {
                var permissions = cordova.plugins.permissions;
                permissions.checkPermission(
                    permissions.ACCESS_FINE_LOCATION,
                    function(status) {
                        if (status.hasPermission) {
                            localRequestLocation(deferred);
                        } else {
                            permissions.requestPermission(
                                permissions.ACCESS_FINE_LOCATION,
                                function (success) {
                                    localRequestLocation(deferred);
                                }, function (error) {
                                    localReject(deferred);
                                });
                        }
                    }, function (error) {
                        permissions.requestPermission(
                            permissions.ACCESS_FINE_LOCATION,
                            function (success) {
                                localRequestLocation(deferred);
                            }, function (error) {
                                localReject(deferred);
                            });
                    });
            } else {
                localRequestLocation(deferred);
            }
        }

        return deferred.promise;
    };

    service.requestPermission = function () {
        var deferred = $q.defer();

        if (cordova.plugins.permissions !== undefined) {
            var permissions = cordova.plugins.permissions;
            permissions.checkPermission(
                permissions.ACCESS_FINE_LOCATION,
                function(status) {
                    if (!status.hasPermission) {
                        permissions.requestPermission(
                            permissions.ACCESS_FINE_LOCATION,
                            function (success) {
                                service.isEnabled = true;
                                deferred.resolve(service.position);
                            }, function (error) {
                                service.isEnabled = false;
                                deferred.reject();
                            });
                    }
                }, function (error) {
                    permissions.requestPermission(
                        permissions.ACCESS_FINE_LOCATION,
                        function (success) {
                            service.isEnabled = true;
                            deferred.resolve(service.position);
                        }, function (error) {
                            service.isEnabled = false;
                            deferred.reject();
                        });
                });
        } else {
            deferred.reject();
        }

        return deferred.promise;
    };

    service.requestLocation = function (success, error) {
        Dialog
            .confirm(
                'Error',
                'We were unable to request your location.<br />Please check that the application is allowed to use the GPS and that your device GPS is on.',
                ['TRY AGAIN', 'DISMISS'],
                -1,
                'location')
            .then(function (success) {
                if (success) {
                    service.isEnabled = true;
                    Loader.show();
                    service
                        .getLocation({timeout: 30000, enableHighAccuracy: false}, true)
                        .then(function (payload) {
                            // GPS is OK!!
                            $rootScope.$broadcast('location.request.success');
                            Loader.hide();
                            Dialog.alert('Success', 'We finally got you location', 'OK', 2350, 'location');

                            if (success &&
                                typeof success === 'function') {
                                try {
                                    success();
                                } catch (e) {
                                    // Silent!
                                }
                            }
                        }, function () {
                            $rootScope.$broadcast('location.request.error');
                            Loader.hide();
                            Dialog
                                .alert(
                                    'Error',
                                    'We were unable to request your location.<br />Please check that the application is allowed to use the GPS and that your device GPS is on.',
                                    'OK',
                                    3700,
                                    'location'
                                );

                            if (error &&
                                typeof error === 'function') {
                                try {
                                    error();
                                } catch (e) {
                                    // Silent!
                                }
                            }
                        });
                }
            });
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
