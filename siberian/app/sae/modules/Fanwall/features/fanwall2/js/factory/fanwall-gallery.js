/**
 * Module FanWall
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.18.20
 */
angular
    .module('starter')
    .factory('FanwallGallery', function ($pwaRequest, $stateParams) {

        var factory = {
            storage: []
        };

        factory.findAll = function (offset, refresh) {
            if (factory.storage.hasOwnProperty($stateParams.value_id)) {
                return $pwaRequest.resolve(factory.storage[$stateParams.value_id]);
            }

            var promise = $pwaRequest.get('fanwall/mobile_gallery/find-all', angular.extend({
                urlParams: {
                    value_id: $stateParams.value_id,
                    offset: offset
                },
                refresh: refresh
            }, factory.extendedOptions));

            promise.then(function (success) {
                factory.storage[$stateParams.value_id] = success;
            });

            return promise;
        };

        return factory;
    });
