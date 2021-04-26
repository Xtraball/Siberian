/**
 * Module FanWall
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.20.9
 */
angular
    .module('starter')
    .factory('Fanwall', function ($pwaRequest, $stateParams, $state, $rootScope, Application) {

    var factory = {
        storage: [],
        lastValueId: null,
        initValueId: null,
        initPostId: null
    };

    factory.loadSettings = function () {
        if (factory.storage.hasOwnProperty($stateParams.value_id)) {
            return $pwaRequest.resolve(factory.storage[$stateParams.value_id]);
        }

        var payload = $pwaRequest.getPayloadForValueId($stateParams.value_id);
        if (payload !== false) {
            factory.storage[$stateParams.value_id] = payload;
            return $pwaRequest.resolve(payload);
        }

        // Otherwise fallback on PWA!
        var promise = $pwaRequest.get('fanwall/mobile_home/load-settings', {
            urlParams: {
                value_id: $stateParams.value_id
            }
        });

        promise.then(function (success) {
            factory.storage[$stateParams.value_id] = success;
        });

        return promise;
    };

    factory.getSettings = function () {
        // First the current valudId if inside social wall
        if (factory.storage.hasOwnProperty($stateParams.value_id)) {
            factory.lastValueId = $stateParams.value_id;
            return factory.storage[$stateParams.value_id].settings;
        }
        // Fallback on the "last" valueId if we go back
        if (factory.storage.hasOwnProperty(factory.lastValueId)) {
            return factory.storage[factory.lastValueId].settings;
        }
        // Then default nulled settings
        return {
            cardDesign: false,
            features: {
                enableGallery: true,
                enableMap: true,
                enableNearby: true,
                enableUserComment: true,
                enableUserLike: true,
                enableUserPost: true,
                enableUserShare: 'none'
            },
            icons: {
                gallery: null,
                map: null,
                nearby: null,
                new: null,
                post: null,
                profile: null
            },
            max_images: 10
        };
    };

    factory.onStart = function () {
        // Do not load if app locked!
        if ($rootScope.app_is_bo_locked ||
            Application.is_locked ||
            $rootScope.app_is_locked) {
            return;
        }

        // Checking start_hash, start hash always has the priority over the session!
        // /fanwall/post/:id
        var hash = HASH_ON_START.match(/\?__goto__=(.*)/);
        if (hash && hash.length >= 2) {
            // We use a short path here!
            var path = hash[1];
            var parts = path.match(/\/fanwall\/post\/([0-9]+)\/([0-9]+)/);
            if (parts && parts.length > 2) {
                // Go to the post page
                factory.initValueId = parts[1];
                factory.initPostId = parts[2];
                $state.go('fanwall-home', {
                    value_id: factory.initValueId
                });
            }
        }
    };

    return factory;
});
