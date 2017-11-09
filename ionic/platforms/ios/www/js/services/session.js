/*global
 angular, localStorage, device
 */

angular.module('starter').service('$session', function ($log, $pwaCache, $q, $window) {
    $log.debug('Init once $session');

    var service = {
        localstorage_key    : 'sb-auth-token',
        session_id          : false,
        device_uid          : null,
        device_width        : 512,
        device_height       : 512,
        device_orientation  : 'portrait',
        is_loaded           : false,
        resolver            : $q.defer()
    };

    /** Be sure the session is loaded */
    Object.defineProperty(service, 'loaded', {
        get: function () {
            if (service.is_loaded) {
                return $q.resolve();
            }
            return service.resolver.promise;
        },
        set: function (value) {
            service.is_loaded = !!value;
            if (service.is_loaded === true) {
                service.resolver.resolve();
            }
        }
    });

    /**
     *
     * @param sessionId
     */
    service.setId = function (sessionId) {
        if ((sessionId === 'undefined') ||
            (sessionId === undefined) ||
            (sessionId === 'null') ||
            (sessionId === null) ||
            (sessionId === '')) {
            $log.error('Not saving invalid session_id: ', sessionId);
            return;
        }

        service.session_id = sessionId;
        service.setItem(service.localstorage_key, sessionId);

        /** Fallback */
        $window.localStorage.setItem('sb-auth-token', sessionId);

        service.setDeviceUid();
    };

    /**
     * @returns string|false
     */
    service.getId = function () {
        if ((service.session_id === 'undefined') ||
            (service.session_id === undefined) ||
            (service.session_id === 'null') ||
            (service.session_id === null) ||
            (service.session_id === '')) {
            return false;
        }
        return service.session_id;
    };

    /**
     *
     */
    service.setDeviceUid = function () {
        if ($window.device === undefined) {
            service.device_uid = 'unknown_' + service.getId();
        } else {
            if ($window.device.platform === 'browser') {
                service.device_uid = 'browser_' + service.getId();
            } else {
                service.device_uid = $window.device.uuid;
            }
        }

        /** And finally if we really don't get it */
        if (service.device_uid ==='""' || service.device_uid === undefined) {
            service.device_uid = 'unknown_' + service.getId();
        }
    };

    service.getDeviceUid = function () {
        return service.device_uid;
    };

    /**
     * clear the current session
     */
    service.clear = function () {
        service.session_id = '';
        service.removeItem(service.localstorage_key);
    };

    /**
     *
     * @param width
     * @param height
     * @returns {{width: number, height: number, orientation: string}}
     */
    service.setDeviceScreen = function (width, height) {
        var orientation = ($window.matchMedia('(orientation: portrait)').matches) ? 'portrait' : 'landscape';

        service.device_width = width;
        service.device_height = height;
        service.device_orientation = orientation;

        return service.getDeviceScreen();
    };

    /**
     *
     * @returns {{width: number, height: number, orientation: string}}
     */
    service.getDeviceScreen = function () {
        return {
            width: service.device_width,
            height: service.device_height,
            orientation: service.device_orientation
        };
    };

    /**
     * save item.
     */
    service.setItem = function (key, value) {
        return $pwaCache.getRegistryCache().setItem(key, value);
    };

    /**
     * get item.
     */
    service.getItem = function (key) {
        return $pwaCache.getRegistryCache().getItem(key);
    };

    /**
     * remove item.
     */
    service.removeItem = function (key) {
        return $pwaCache.getRegistryCache().removeItem(key);
    };

    /**
     * Init once
     */
    service.setDeviceScreen($window.innerWidth, $window.innerHeight);
    service.getItem(service.localstorage_key)
        .then(function (value) {
            var fallback = $window.localStorage.getItem('sb-auth-token');

            if ((value !== null) && (value !== undefined)) {
                $log.debug('Set once $session from pwaRegistry on start: ', value);
                service.setId(value);

                // Don't forget to log-in the customer.!
            } else if ((fallback !== null) && (fallback !== undefined)) {
                $log.debug('Set once $session from fallback localstorage on start: ', fallback);

                service.setId(fallback);
            }

            if (service.device_uid === null) {
                service.setDeviceUid();
            }

            service.loaded = true;
        });

    return service;
});
