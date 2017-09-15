/* global
 App, angular, _
 */

/**
 * Pages
 *
 * @author Xtraball SAS
 */
angular.module('starter').factory('Pages', function ($pwaRequest, $rootScope, $q, $log, SB) {
    /**
     * @type {{data: null, touched: Array, last_refresh: number, refresh_interval: number}}
     */
    var factory = {
        data: null,
        features: [],
        touched: [],
        last_refresh: -1,
        refresh_interval: $rootScope.isOverview ? 0 : 300,
        is_refreshing: false
    };


    var _ready = false;
    var _ready_resolver = $q.defer();

    Object.defineProperty(factory, 'ready', {
        get: function () {
            if (_ready) {
                $log.info('Pages ready, resolving promise');
                return $q.resolve();
            }
            return _ready_resolver.promise;
        },
        set: function (value) {
            _ready = !!value;
            if (_ready === true) {
                $log.info('Pages ready, resolving promise');
                _ready_resolver.resolve();
            }
        }
    });


    /**
     * Populate Application service on load
     *
     * @param data
     */
    factory.populate = function (data) {
        factory.data = angular.copy(data);
        factory.features = angular.copy(data.pages);
        factory.data.pages = _.filter(data.pages, { homepage: true });

        if (data.hasOwnProperty('touched')) {
            factory.touched = data.touched;
            factory.last_refresh = Math.trunc(Date.now() / 1000);
        }

        $rootScope.$broadcast(SB.EVENTS.CACHE.pagesReload);

        factory.ready = true;

        return $q.resolve(data);
    };

    /**
     * Get active pages for modal
     *
     * @returns {Array}
     */
    factory.getActivePages = function () {
        return _.filter(factory.features, {
            is_active: true
        });
    };

    /**
     * Refresh pages.
     */
    factory.refresh = function () {
        $pwaRequest.get('front/mobile/pagesv2', {
            refresh: true
        }).then(function (data) {
            factory.features = angular.copy(data.pages);
            factory.data.pages = _.filter(data.pages, { homepage: true });
        });
    };

    /**
     *
     * @param touched
     */
    factory.updateTouched = function () {
        var deferred = $q.defer();

        var past_refresh = (factory.last_refresh + factory.refresh_interval);
        var now = Math.trunc(Date.now() / 1000);

        if (!factory.is_refreshing && (past_refresh < now)) {
            factory.is_refreshing = true;
            $pwaRequest.get('front/mobile/touched', {
                refresh: true
            }).then(function (data) {
                var will_refresh = false;
                angular.forEach(data.touched, function (feature, value_id) {
                    if (factory.touched.hasOwnProperty(value_id) &&
                        ((factory.touched[value_id].touched_at < feature.touched_at) || (feature.expires_at < now))) {
                        // will reload pages payload, we can stop on first positive.
                        will_refresh = true;
                    }
                });

                factory.touched = data.touched;
                factory.last_refresh = now;

                deferred.resolve(factory.touched);

                if (will_refresh) {
                    factory.refresh();
                }

                factory.is_refreshing = false;
            });
        } else {
            deferred.resolve(factory.touched);
        }

        return deferred.promise;
    };

    /**
     *
     * @param value_id
     * @returns {Number touched_at, Number expires_at}
     */
    factory.getForValueId = function (value_id) {
        factory.updateTouched();

        if (factory.touched.hasOwnProperty(value_id)) {
            return factory.touched[value_id];
        }
            return {
                touched_at: -1,
                expires_at: -1
            };
    };

    /**
     * @param value_id
     * @returns {number}
     */
    factory.getLayoutIdForValueId = function (value_id) {
        return _.get(_.filter(factory.features, function (feature) {
            return (feature.value_id == value_id);
        })[0], 'layout_id', 1);
    };

    /**
     *
     * @param value_id
     */
    factory.getTouchedAt = function (value_id) {
        return factory.getForValueId(value_id).touched_at;
    };

    /**
     *
     * @param value_id
     */
    factory.getExpiresAt = function (value_id) {
        return factory.getForValueId(value_id).expires_at;
    };

    /**
     * @param value_id
     * @returns {number}
     */
    factory.getLayoutIdForValueId = function (value_id) {
        return _.get(_.filter(factory.features, { value_id: value_id * 1 })[0], 'layout_id', 1);
    };

    /**
     *
     * @param value_id
     * @returns {*}
     */
    factory.getPayloadForValueId = function (value_id) {
        return _.get(_.filter(factory.features, { value_id: value_id * 1 })[0], 'embed_payload', false);
    };


    return factory;
});
